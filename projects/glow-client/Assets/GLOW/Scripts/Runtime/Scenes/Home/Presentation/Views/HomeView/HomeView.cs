using GLOW.Scenes.Home.Presentation.Components;
using SoftMasking;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public sealed class HomeView : UIView
    {
        [SerializeField] RectTransform _contentRoot;
        [SerializeField] RectTransform _bottomOverlapContentRoot;
        [SerializeField] RectTransform _fullScreenOverlapContentRoot;

        [SerializeField] HomeHeader _header;
        [SerializeField] HomeFooter _footer;
        [SerializeField] HomeBackground _background;
        [Header("タップブロック")]
        [SerializeField] GameObject _tapBlock;
        [SerializeField] CanvasGroup _tapBlockCanvasGroup;
        [SerializeField] SoftMask _softMask;

        [Header("チュートリアルで使用")]
        [SerializeField] Button _footerGachaButton;

        public RectTransform ContentRoot => _contentRoot;
        public RectTransform BottomOverlapContentRoot => _bottomOverlapContentRoot; // フッターに被せて表示するコンテンツのルート
        public RectTransform FullScreenOverlapContentRoot => _fullScreenOverlapContentRoot; // フルスクリーンで表示するコンテンツのルート

        public HomeHeader Header => _header;
        public HomeFooter Footer => _footer;
        public HomeBackground Background => _background;

        public GameObject TapBlock => _tapBlock;
        public CanvasGroup TapBlockCanvasGroup => _tapBlockCanvasGroup;
        public SoftMask SoftMask => _softMask;

        public Button FooterGachaButton => _footerGachaButton;

        protected override void Awake()
        {
            base.Awake();
            _tapBlock.SetActive(false);
        }

        public void InitializeView()
        {
            _header.SetAvatarBadge(false);
            _header.SetEmblemBadge(false);
            _header.SetStaminaBadge(false);

            _footer.CharacterBadge = false;
            _footer.GachaBadge = false;
            _footer.ContentBadge = false;
            _footer.HomeBadge = false;
            _footer.ShopBadge = false;
        }
    }
}
