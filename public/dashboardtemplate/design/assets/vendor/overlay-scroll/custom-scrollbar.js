$(function () {
	$(".sidebarMenuScroll").overlayScrollbars({
		scrollbars: {
			visibility: "auto",
			autoHide: "scroll",
			autoHideDelay: 200,
			dragScrolling: true,
			clickScrolling: false,
			touchSupport: true,
			snapHandle: false,
		},
	});
});

$(function () {
	// Fase 8 (UI1/UI3): no aplicar overlayScrollbars a .content-wrapper-scroll
	// para evitar barras duplicadas con la del navegador y scrolls anidados.
	// Se delega el scroll al documento (gestionado por CSS en layouts/admin.blade.php).
	// Mantener desactivado salvo cambio explícito.
});

// Scroll 330
$(function () {
	$(".scroll333").overlayScrollbars({
		scrollbars: {
			visibility: "auto",
			autoHide: "scroll",
			autoHideDelay: 200,
			dragScrolling: true,
			clickScrolling: false,
			touchSupport: true,
			snapHandle: false,
		},
	});
});

// Scroll 370
$(function () {
	$(".scroll370").overlayScrollbars({
		scrollbars: {
			visibility: "auto",
			autoHide: "scroll",
			autoHideDelay: 200,
			dragScrolling: true,
			clickScrolling: false,
			touchSupport: true,
			snapHandle: false,
		},
	});
});

// Scroll 360
$(function () {
	$(".scroll360").overlayScrollbars({
		scrollbars: {
			visibility: "auto",
			autoHide: "scroll",
			autoHideDelay: 200,
			dragScrolling: true,
			clickScrolling: false,
			touchSupport: true,
			snapHandle: false,
		},
	});
});

// Scroll 160
$(function () {
	$(".scroll160").overlayScrollbars({
		scrollbars: {
			visibility: "auto",
			autoHide: "scroll",
			autoHideDelay: 200,
			dragScrolling: true,
			clickScrolling: false,
			touchSupport: true,
			snapHandle: false,
		},
	});
});
