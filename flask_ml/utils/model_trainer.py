import joblib
import pandas as pd
import numpy as np
from prophet import Prophet
from sklearn.metrics import mean_absolute_error, mean_squared_error
from .preprocessor import load_data, prepare_timeseries, get_commodities
import os
from datetime import timedelta
import numpy as np

class CommodityPredictor:
    def __init__(self):
        self.models = {}
        self.accuracies = {}
        self.data_history = {}
    
    def train_all(self, csv_path='data/raw/sample_pangan_makanan_jember_sample.csv'):
        """Train Prophet model for all commodities"""
        df = load_data(csv_path)
        commodities = get_commodities(df)
        
        print(f"Training {len(commodities)} commodities...")
        
        for commodity in commodities:
            print(f"Training {commodity}...")
            ts_df = prepare_timeseries(df, commodity)
            
            if len(ts_df) < 5:  # Need min data
                print(f"  Skipping {commodity}: insufficient data")
                continue
            
            # Split: 80% train, 20% test (last 20%)
            split_idx = int(len(ts_df) * 0.8)
            train_df = ts_df.iloc[:split_idx]
            test_df = ts_df.iloc[split_idx:]
            
            # Train Prophet
            model = Prophet(
                daily_seasonality=True,
                weekly_seasonality=True,
                yearly_seasonality=False,  # Sample data too short
                changepoint_prior_scale=0.05
            )
            model.fit(train_df)
            
            # Test predictions
            future = model.make_future_dataframe(periods=len(test_df))
            forecast = model.predict(future)
            
            test_pred = forecast['yhat'].iloc[-len(test_df):].values
            test_actual = test_df['y'].values
            
            mae = mean_absolute_error(test_actual, test_pred)
            rmse = np.sqrt(mean_squared_error(test_actual, test_pred))
            mape = np.mean(np.abs((test_actual - test_pred) / test_actual)) * 100
            
            accuracy = 100 - mape
            
            # Store
            self.models[commodity] = model
            self.accuracies[commodity] = {
                'mae': float(mae), 'rmse': float(rmse), 
                'mape': float(mape), 'accuracy': float(accuracy)
            }
            self.data_history[commodity] = ts_df
            
            print(f"  {commodity}: Accuracy {accuracy:.1f}% (MAE: {mae:.0f})")
        
        # Save all models
        self.save_models()
        return self.accuracies
    
    def predict(self, commodity, periods=7):
        """Predict next N days for commodity"""
        if commodity not in self.models:
            raise ValueError(f"No model for {commodity}")
        
        model = self.models[commodity]
        future = model.make_future_dataframe(periods=periods)
        forecast = model.predict(future)
        
        future_dates = forecast['ds'].tail(periods).dt.strftime('%Y-%m-%d').tolist()
        predictions = forecast['yhat'].tail(periods).tolist()
        lower = forecast['yhat_lower'].tail(periods).tolist()
        upper = forecast['yhat_upper'].tail(periods).tolist()
        
        current_price = self.data_history[commodity]['y'].iloc[-1]
        avg_future = np.mean(predictions)
        trend = (avg_future - current_price) / current_price * 100
        
        return {
            'dates': future_dates,
            'predictions': predictions,
            'confidence_lower': lower,
            'confidence_upper': upper,
            'current_price': float(current_price),
            'avg_future_price': float(avg_future),
            'price_trend_pct': float(trend)
        }
    
    def get_recommendation(self, commodity, konsumsi_mingguan, predictions_data):
        """Generate buy recommendation"""
        current_price = predictions_data['current_price']
        predictions = predictions_data['predictions']
        avg_future = predictions_data['avg_future_price']
        
        total_now = konsumsi_mingguan * current_price
        total_future_avg = konsumsi_mingguan * avg_future
        diff_pct = (avg_future - current_price) / current_price * 100
        
        if diff_pct > 2:  # If future >2% higher
            recommendation = "BELI SEKARANG"
            reason = f"Harga diprediksi naik {diff_pct:.1f}% minggu depan. Hemat Rp {total_future_avg - total_now:,.0f}"
        elif diff_pct < -2:
            recommendation = "TUNGGU 3-4 HARI"
            reason = f"Harga diprediksi turun {abs(diff_pct):.1f}%. Potensi hemat Rp {total_now - total_future_avg:,.0f}"
        else:
            recommendation = "HARGA STABIL"
            reason = f"Fluktuasi <2%. Harga rata-rata 7 hari: Rp{avg_future:,.0f}/unit"
        
        return {
            'recommendation': recommendation,
            'total_cost_now': float(total_now),
            'total_cost_recommended': float(total_future_avg),
            'reason': reason,
            'accuracy': self.accuracies[commodity]['accuracy']
        }
    
    def save_models(self, path='models/commodity_models.pkl'):
        """Save all models and metadata"""
        os.makedirs(os.path.dirname(path), exist_ok=True)
        joblib.dump({
            'models': self.models,
            'accuracies': self.accuracies,
            'data_history': {k: v.to_dict() for k,v in self.data_history.items()}
        }, path)
    
    def load_models(self, path='models/commodity_models.pkl'):
        """Load saved models"""
        if os.path.exists(path):
            data = joblib.load(path)
            self.models = data['models']
            self.accuracies = data['accuracies']
            # Reconstruct data_history
            self.data_history = {}
            for k, v in data['data_history'].items():
                df = pd.DataFrame(v)
                df['ds'] = pd.to_datetime(df['ds'])
                self.data_history[k] = df
            print(f"Loaded {len(self.models)} models")

