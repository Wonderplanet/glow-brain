import './bootstrap';

document.addEventListener('DOMContentLoaded', function () {
    sidebarSearch();
});

function sidebarSearch()
{
    // 検索用の入力フォーム
    const navFilter = document.getElementById('navigation-search');

    // 各メニュー
    const navItems = Array.from(document.querySelectorAll('.fi-sidebar-item'));
    // 各メニューの親要素(開閉できるグループ)
    const groupButtons = Array.from(document.querySelectorAll('.fi-sidebar-group'));

    // 入力イベントで発火するイベント登録
    navFilter.addEventListener('input', function (event) {
        const filterValue = event.target.value;

        // 入力テキストと合致するメニューのみ表示
        navItems.forEach(function (navItem) {
            const spanElement = navItem.querySelector('span');
            const navItemText = spanElement.textContent;

            if (navItemText.includes(filterValue)) {
                navItem.style.display = '';
            } else {
                navItem.style.display = 'none';
            }
        });

        // 表示するメニューがあるグループボタンのみ表示
        groupButtons.forEach(function (groupButton) {
            const listItems = Array.from(groupButton.querySelectorAll('.fi-sidebar-item'));

            let visibleCount = 0;
            listItems.forEach(function (listItem) {
                if (listItem.style.display !== 'none') {
                    visibleCount++;
                }
            });

            if (visibleCount === 0) {
                groupButton.style.display = 'none';
            } else {
                groupButton.style.display = '';
            }
        });
    });
}
