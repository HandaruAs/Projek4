class UserModel {
  final String id;
  final String name;
  final String email;
  final String role;
  final String phone;
  final String address;
  final String avatarUrl; // ← tambah

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.phone     = '',
    this.address   = '',
    this.avatarUrl = '', // ← tambah
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id:        json['id']?.toString()         ?? '',
      name:      json['name']?.toString()       ?? '',
      email:     json['email']?.toString()      ?? '',
      role:      json['role']?.toString()       ?? 'user',
      phone:     json['phone']?.toString()      ?? '',
      address:   json['address']?.toString()    ?? '',
      avatarUrl: json['avatar_url']?.toString() ?? '', // ← tambah
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id':         id,
      'name':       name,
      'email':      email,
      'role':       role,
      'phone':      phone,
      'address':    address,
      'avatar_url': avatarUrl, // ← tambah
    };
  }

  // ← tambah method copyWith
  UserModel copyWith({
    String? id,
    String? name,
    String? email,
    String? role,
    String? phone,
    String? address,
    String? avatarUrl,
  }) {
    return UserModel(
      id:        id        ?? this.id,
      name:      name      ?? this.name,
      email:     email     ?? this.email,
      role:      role      ?? this.role,
      phone:     phone     ?? this.phone,
      address:   address   ?? this.address,
      avatarUrl: avatarUrl ?? this.avatarUrl,
    );
  }
}