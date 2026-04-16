import pandas as pd
import numpy as np
from datetime import datetime
import os
import re

def load_data(use_mongo=True, csv_path='data/raw/sample_pangan_makanan_jember_sample.csv'):
    """Load data from MongoDB or CSV"""
    if use_mongo:
        try:
            from pymongo import MongoClient
            client = MongoClient('mongodb://127.0.0.1:27017/')
            db = client['monitoring_harga_pangan']
            collection = db['price_histories']  # Your collection!
            
            data = list(collection.find({
                '$or': [
                    {'commodity_name': {'$regex': 'beras|gula|minyak|daging|telur|ayam|sapi|ikan', '$options': 'i'}},
                    {'category': {'$regex': 'BERAS|GULA|MINYAK|DAGING|TELUR|AYAM|SAPI|IKAN', '$options': 'i'}}
                ]
            }).sort('date', 1).limit(50000))
            
            if not data:
                print("MongoDB empty, falling back to CSV")
                use_mongo = False
            
            df = pd.DataFrame(data)
            if not df.empty:
                print(f"Loaded {len(df)} records from MongoDB")
                # CLEAN DATA HERE for MongoDB
                df['komoditas'] = df['commodity_name'].fillna(df.get('category', '')).astype(str).str.strip().str.upper()
                df['tanggal'] = pd.to_datetime(df['date'])
                df['harga_sekarang'] = pd.to_numeric(df['harga_sekarang'], errors='coerce')
                df['harga_lama'] = pd.to_numeric(df['harga_lama'], errors='coerce')
                
                # Remove invalid
                df = df.dropna(subset=['harga_sekarang', 'komoditas', 'tanggal'])
                df = df[df['harga_sekarang'] > 0]
                
                print(f"Cleaned MongoDB data: {len(df)} valid records")
                return df.sort_values(['komoditas', 'tanggal'])
        except Exception as e:
            print(f"MongoDB error: {e}, falling back to CSV")
    
    # Fallback CSV
    if not os.path.exists(csv_path):
        raise FileNotFoundError(f"Data file not found: {csv_path}")
    
    df = pd.read_csv(csv_path)
    print(f"Loaded {len(df)} records from CSV: {csv_path}")
    
    # Clean CSV
    df['komoditas'] = df['komoditas'].astype(str).str.strip().str.upper()
    df['tanggal'] = pd.to_datetime(df['tanggal'])
    df['harga_sekarang'] = pd.to_numeric(df['harga_sekarang'], errors='coerce')
    df['harga_lama'] = pd.to_numeric(df['harga_lama'], errors='coerce')
    
    # Remove invalid prices
    df = df.dropna(subset=['harga_sekarang', 'komoditas', 'tanggal'])
    df = df[df['harga_sekarang'] > 0]
    
    return df.sort_values(['komoditas', 'tanggal'])

def prepare_timeseries(df, commodity):
    """Prepare data for Prophet: ds (date), y (price)"""
    commodity_df = df[df['komoditas'] == commodity].copy()
    commodity_df = commodity_df[['tanggal', 'harga_sekarang']].rename(
        columns={'tanggal': 'ds', 'harga_sekarang': 'y'}
    )
    commodity_df['ds'] = pd.to_datetime(commodity_df['ds'])
    return commodity_df.sort_values('ds')

def get_commodities(df):
    """Get unique commodities"""
    return df['komoditas'].unique().tolist()

