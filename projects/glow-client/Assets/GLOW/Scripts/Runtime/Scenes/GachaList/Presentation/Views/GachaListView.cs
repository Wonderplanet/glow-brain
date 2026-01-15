using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.CustomCarousel;
using GLOW.Scenes.GachaContent.Presentation.Views;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.GachaList.Presentation.Views
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-5_ガシャ一覧画面
    /// </summary>
    public class GachaListView : UIView
    {
        [Header("ボタン/未選別")]
        [SerializeField] VerticalLayoutGroup _verticalLayoutGroup;
        [SerializeField] ChildScaler _childScaler;
        [SerializeField] CanvasGroup _canvasGroup;
        [Header("ボタン/提供割合・ラインナップ")]
        [SerializeField] Button _rationButton;
        [SerializeField] Button _lineupButton;
        [Header("ボタン/ガシャ詳細")]
        [SerializeField] Button _detailButton;
        [Header("ボタン/ユニット必殺ワザ・詳細")]
        [SerializeField] Button _specialAttackButton;
        [SerializeField] Button _pickupStatusDetailButton;
        [Header("カルーセル")]
        [SerializeField] GlowCustomInfiniteCarouselView _footerCarouselView;
        [SerializeField] Button _leftButton;
        [SerializeField] Button _rightButton;
        [SerializeField] GameObject _carouselTapBlockObject;
        [SerializeField] float _maxDistanceMargin = 2.5f;
        [SerializeField] float _cellSizeMargin = 0.3f;
        [Header("コンテンツView")]
        [SerializeField] GachaContentView _gachaContentView;

        public GlowCustomInfiniteCarouselView FooterCarouselView => _footerCarouselView;
        public float MaxDistanceMargin => _maxDistanceMargin;
        public float CellSizeMargin => _cellSizeMargin;
        public GachaContentView GachaContentView => _gachaContentView;


        public void SetUpGachaDetailButton(bool showRatio, bool hasDetail)
        {
            _rationButton.gameObject.SetActive(showRatio);
            _lineupButton.gameObject.SetActive(!showRatio);
            _detailButton.gameObject.SetActive(hasDetail);
        }

        public void SetCarouselVisibility(bool isVisible)
        {
            _leftButton.gameObject.SetActive(isVisible);
            _rightButton.gameObject.SetActive(isVisible);
            _carouselTapBlockObject.SetActive(!isVisible);
        }

        public void UpdateUnitButtons(bool isVisible)
        {
            _specialAttackButton.gameObject.SetActive(isVisible);
            _pickupStatusDetailButton.gameObject.SetActive(isVisible);
        }
    }
}
