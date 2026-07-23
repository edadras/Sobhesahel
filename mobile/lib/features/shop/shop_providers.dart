// Riverpod async providers for the shop feature. These live in the feature
// folder (not the data-layer core) and simply wrap ShopRepository so the shop
// screens can `ref.watch` product/order lists with loading + error states.

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../data/models/models.dart';
import '../../data/providers.dart';

/// Product listing keyed by the active sort ('newest' | 'cheapest').
final shopProductsProvider =
    FutureProvider.autoDispose.family<List<Product>, String>((ref, sort) {
  return ref.watch(shopRepositoryProvider).products(sort: sort);
});

/// A single product by slug (used by the detail screen).
final shopProductProvider =
    FutureProvider.autoDispose.family<Product, String>((ref, slug) {
  return ref.watch(shopRepositoryProvider).product(slug);
});

/// The signed-in member's orders.
final shopOrdersProvider = FutureProvider.autoDispose<List<Order>>((ref) {
  return ref.watch(shopRepositoryProvider).orders();
});
