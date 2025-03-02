<?php
require_once "../core/Template.php";
require_once "../controller/ProductService.php";
require_once "../controller/CartService.php";

$products = ProductService::find_all();

$user = Session::get_session('__user');

if (isset($_POST['add_to_cart'])) {

	// Kiểm tra xem người dùng đã đăng nhập hay chưa
	if (!$user) {
		header('location: login.php');
		exit();
	} else {
		// Lọc và lấy dữ liệu từ form
		$user_id = filter_var($_POST['user_id'], FILTER_SANITIZE_STRING);
		$pid = filter_var($_POST['id'], FILTER_SANITIZE_STRING); // Giả sử 'pid' là ID sản phẩm
		$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
		$price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
		$image = filter_var($_POST['image'], FILTER_SANITIZE_STRING);
		$qty = filter_var($_POST['qty'], FILTER_SANITIZE_NUMBER_INT);

		// Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
		$check_cart = CartService::getCheckCart($pid, $user_id); // Giả sử `getCheckCart` kiểm tra cả theo `pid` và `user_id`

		if ($check_cart) {
			// Nếu sản phẩm đã có trong giỏ hàng, cộng thêm số lượng
			$new_qty = $check_cart['quantity'] + $qty;
			$update_cart = CartService::updateCartQuantity($pid, $user_id, $new_qty);

			if ($update_cart) {
				$message[] = 'Đã cập nhật số lượng sản phẩm trong giỏ hàng!';
			} else {
				$message[] = 'Có lỗi xảy ra khi cập nhật giỏ hàng!';
			}
		} else {
			// Nếu sản phẩm chưa có trong giỏ hàng, thêm mới
			$data = [
				'user_id' => $user_id,
				'pid' => $pid, // ID sản phẩm
				'name' => $name,
				'price' => $price,
				'quantity' => $qty,
				'image' => $image
			];

			// Sử dụng hàm db_insert để chèn dữ liệu
			$insert_id = CartService::create($data);

			if ($insert_id) {
				$message[] = 'Đã thêm vào giỏ hàng!';
			} else {
				$message[] = 'Có lỗi xảy ra khi thêm vào giỏ hàng!';
			}
		}
	}
}

?>

<?php Template::head("Tour"); ?>

<?php Template::header(); ?>

<div class="heading">
	<h3>EXPLORE THE ULTIMATE TECHNOLOGY</h3>
	<p><a href="<?= route("/index.php") ?>">Trang chủ</a> <span> / Sản phẩm</span></p>
</div>

<!-- menu section starts  -->

<section class="products">

	<h1 class="title">LATEST DEVICES</h1>

	<div class="box-container">

		<?php foreach ($products as $row) : ?>
			<form action="" method="post" class="box">
				<input type="hidden" name="id" value="<?= $row['id']; ?>">
				<input type="hidden" name="user_id" value="<?= $user['id']; ?>">
				<input type="hidden" name="name" value="<?= $row['name']; ?>">
				<input type="hidden" name="price" value="<?= $row['price']; ?>">
				<input type="hidden" name="image" value="<?= $row['image']; ?>">
				<a href="quick_view.php?id=<?= $row['id']; ?>" class="fas fa-eye"></a>
				<button type="submit" class="fas fa-shopping-cart" name="add_to_cart"></button>
				<img src="<?= $row['image']; ?>" alt="">
				<a href="<?= route('/views/category.php?category=' . $row['category']) ?>" class="cat"><?= $row['category']; ?></a>
				<div class="name"><?= $row['name']; ?></div>
				<div class="flex">
					<div class="price"><?php echo currency_format($row['price']); ?></div>

					<input type="number" name="qty" class="qty" min="1" max="99" value="1" maxlength="2">
				</div>
			</form>
		<?php endforeach; ?>

	</div>

</section>

<?php Template::footer(); ?>

<?php Template::foot(); ?>