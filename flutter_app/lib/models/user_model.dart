class UserModel {
  final String id;
  final String name;
  final String email;
  final String role;
  final String phone;
  final String address;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.phone = '',
    this.address = '',
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id:      json['id']?.toString()      ?? '',
      name:    json['name']?.toString()    ?? '',
      email:   json['email']?.toString()   ?? '',
      role:    json['role']?.toString()    ?? 'user',
      phone:   json['phone']?.toString()   ?? '',
      address: json['address']?.toString() ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id':      id,
      'name':    name,
      'email':   email,
      'role':    role,
      'phone':   phone,
      'address': address,
    };
  }
}