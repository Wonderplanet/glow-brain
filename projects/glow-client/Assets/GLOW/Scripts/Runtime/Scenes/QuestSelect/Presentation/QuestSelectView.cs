using System.Collections;
using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.CustomCarousel;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.QuestSelect.Presentation.QuestDifficultySelect.Component;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.QuestSelect.Presentation
{
    public sealed class QuestSelectView : UIView
    {
        [SerializeField] CanvasGroup _scriptEditCanvasGroup;
        [Header("クエスト情報")]
        [SerializeField] UIText _questName;
        [SerializeField] UIText _flavorText;
        [SerializeField] ScrollRect _flavorTextScrollRect;
        [SerializeField] GameObject _questLockObject;
        [Header("カルーセル")]
        [SerializeField] GlowCustomInfiniteCarouselView _carouselView;
        [SerializeField] float _maxDistanceMargin = 2.5f;
        [SerializeField] float _cellSizeMargin = 0.3f;
        [Header("左右ボタン")]
        [SerializeField] Button _leftButton;
        [SerializeField] Button _rightButton;
        [Header("ヘッダーレイアウト調整用")]
        [SerializeField] RectTransform _titleBaseTopAreaRect;
        [SerializeField] RectTransform _titleBaseBottomAreaRect;
        [SerializeField] RectTransform _titleAreaRect;
        [Header("ボタンレイアウト調整用")]
        [SerializeField] RectTransform _buttonTopBaseAreaRect;
        [SerializeField] RectTransform _buttonBottomBaseAreaRect;
        [SerializeField] RectTransform _selectButtonAreaRect;
        [Header("クエスト難易度選択用")]
        [SerializeField] QuestDifficultyButtonListComponent _questDifficultyButtonList;

        [Header("キャンペーン")]
        [SerializeField] CampaignBalloonMultiSwitcherComponent _normalCampaignBalloonSwitcher;
        [SerializeField] CampaignBalloonMultiSwitcherComponent _hardCampaignBalloonSwitcher;
        [SerializeField] CampaignBalloonMultiSwitcherComponent _extraCampaignBalloonSwitcher;

        public GlowCustomInfiniteCarouselView CarouselView => _carouselView;
        public UIText QuestName => _questName;
        public UIText FlavorText => _flavorText;
        public ScrollRect FlavorTextScrollRect => _flavorTextScrollRect;
        public GameObject QuestLockObject => _questLockObject;
        public CanvasGroup CanvasGroup => _scriptEditCanvasGroup;
        public float MaxDistanceMargin => _maxDistanceMargin;
        public float CellSizeMargin => _cellSizeMargin;
        public Button LeftButton => _leftButton;
        public Button RightButton => _rightButton;
        public QuestDifficultyButtonListComponent QuestDifficultyButtonListComponent => _questDifficultyButtonList;
        
        bool _isCampaignBalloonHidden;

        public void FixContentLayout()
        {
            StartCoroutine(SetMidPoint());
            StartCoroutine(UpdateSelectButtonArea());
        }

        IEnumerator SetMidPoint()
        {
            //ViewWillAppear > 調整を行うとき、1フレーム目ではSafeArea対象外の位置にいる
            //そのため1フレーム待ってから処理を実行する。

            // Debug.Log("parent: " + this.transform.parent);
            yield return new WaitForEndOfFrame();
            // Debug.Log("parent: " + this.transform.parent);

            // NOTE: AとBの距離を求める
            var topAreaLocalPosition = _titleBaseTopAreaRect.localPosition;
            var distance = topAreaLocalPosition - _titleBaseBottomAreaRect.localPosition;
            _titleAreaRect.transform.localPosition = new Vector2(
                topAreaLocalPosition.x,
                _titleBaseBottomAreaRect.transform.localPosition.y + (distance.y / 2));
        }

        IEnumerator UpdateSelectButtonArea()
        {
            yield return new WaitForEndOfFrame();

            // NOTE: AとBの距離を求める
            var topAreaLocalPosition = _buttonTopBaseAreaRect.localPosition;
            var distance = topAreaLocalPosition - _buttonBottomBaseAreaRect.localPosition;
            _selectButtonAreaRect.transform.localPosition = new Vector2(
                topAreaLocalPosition.x,
                _buttonBottomBaseAreaRect.transform.localPosition.y + (distance.y / 2));
        }

        //レイアウト確認用
        // void Update()
        // {
        //     var a = _titleBaseTopAreaRect.localPosition - _titleBaseBottomAreaRect.localPosition;
        //     _titleAreaRect.transform.localPosition = new Vector2(
        //         _titleBaseTopAreaRect.localPosition.x,
        //         _titleBaseBottomAreaRect.transform.localPosition.y + (a.y / 2));
        //     // NOTE: AとBの距離を求める
        //     var b = _buttonTopBaseAreaRect.localPosition - _buttonBottomBaseAreaRect.localPosition;
        //     _selectButtonAreaRect.transform.localPosition = new Vector2(
        //         _buttonTopBaseAreaRect.localPosition.x,
        //         _buttonBottomBaseAreaRect.transform.localPosition.y + (b.y / 2));
        // }

        public void SetUpCampaignBalloons(
            IReadOnlyList<CampaignViewModel> normalCampaignViewModels,
            IReadOnlyList<CampaignViewModel> hardCampaignViewModels,
            IReadOnlyList<CampaignViewModel> extraCampaignViewModels)
        {
            if (_isCampaignBalloonHidden)
            {
                _normalCampaignBalloonSwitcher.Hidden = true;
                _hardCampaignBalloonSwitcher.Hidden = true;
                _extraCampaignBalloonSwitcher.Hidden = true;
                return;
            }

            _normalCampaignBalloonSwitcher.SetUpCampaignBalloons(normalCampaignViewModels);
            _hardCampaignBalloonSwitcher.SetUpCampaignBalloons(hardCampaignViewModels);
            _extraCampaignBalloonSwitcher.SetUpCampaignBalloons(extraCampaignViewModels);
        }

        public void SetActiveCampaignBalloon(bool isActive)
        {
            _isCampaignBalloonHidden = !isActive;
            
            _normalCampaignBalloonSwitcher.Hidden = !isActive;
            _hardCampaignBalloonSwitcher.Hidden = !isActive;
            _extraCampaignBalloonSwitcher.Hidden = !isActive;
        }
    }
}
