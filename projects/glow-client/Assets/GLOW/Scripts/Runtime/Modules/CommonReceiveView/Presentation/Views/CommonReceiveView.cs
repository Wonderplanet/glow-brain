using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.ValueObject;
using TMPro;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Modules.CommonReceiveView.Presentation.Views
{
    public class CommonReceiveView : UIView
    {
        [SerializeField] PreConversionPlayerResourceIconList _iconList;
        [SerializeField] TextMeshProUGUI _closeText;
        [SerializeField] UIObject _scrollRoot;
        [SerializeField] RectTransform _scrollRootRectTransform;
        [SerializeField] UIText _rewardTitleText;
        [SerializeField] UIObject _descriptionTextObject;
        [SerializeField] UIText _descriptionText;
        [SerializeField] CanvasGroup _descriptionTextCanvasGroup;

        public Action<PlayerResourceIconViewModel> OnPlayerResourceIconTapped { get; set; }

        Tween _tween;

        // スクロールの最大サイズ
        const float MaxScrollHeight = 466;
        // 1列分のサイズ
        const float ScrollColumnHeight = 148;
        // セルの幅
        const float CellWidth = 124;
        // セルの間隔
        const float CellSpace = 22;

        public void SetupScrollRectSize(IReadOnlyList<PlayerResourceIconWithPreConversionViewModel> iconViewModels)
        {
            _closeText.enabled = false;
            var padding = _iconList.Padding;

            if (iconViewModels.Count <= 0) return;
            if (iconViewModels.Count < 5)
            {
                _scrollRootRectTransform.sizeDelta = new Vector2(
                    (CellWidth * (iconViewModels.Count) + (CellSpace * (iconViewModels.Count - 1) + padding.left + padding.right)),
                    _scrollRootRectTransform.sizeDelta.y
                );
            }
            else
            {
                _scrollRootRectTransform.sizeDelta = new Vector2(
                    (CellWidth * 5 + (CellSpace * 4) + padding.left + padding.right),
                    _scrollRootRectTransform.sizeDelta.y
                );
            }

            var rowCorrection = 0;
            if (iconViewModels.Count % 5 != 0) rowCorrection = 1;

            // 表示アイテム数に応じてスクロールの高さ調整
            var scrollSize = ScrollColumnHeight * ((float)iconViewModels.Count / 5 + rowCorrection) + CellSpace;
            if (scrollSize > MaxScrollHeight) scrollSize = MaxScrollHeight;
            _scrollRoot.RectTransform.sizeDelta = new Vector2(_scrollRoot.RectTransform.sizeDelta.x, scrollSize);
        }

        public void SetAcquiredPlayerResources(IReadOnlyList<PlayerResourceIconWithPreConversionViewModel> iconViewModels, Action onComplete)
        {
            _iconList.OnPlayerResourceIconTapped = OnPlayerResourceIconTapped;
            _iconList.SetupAndReload(iconViewModels, true, 3, onComplete:onComplete);

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_004);
        }

        public void SetRewardTitleText(RewardTitle rewardTitle)
        {
            _rewardTitleText.SetText(rewardTitle.Value);
        }

        public void SetDescriptionText(ReceivedRewardDescription description)
        {
            _descriptionText.SetText(description.Value);
            _descriptionTextObject.Hidden = description.IsEmpty();
            LayoutRebuilder.ForceRebuildLayoutImmediate(_descriptionText.RectTransform);
        }

        public async UniTask FadeInDescriptionText(CancellationToken cancellationToken)
        {
            _tween?.Kill();

            _descriptionTextCanvasGroup.alpha = 0.0f;
            _descriptionText.Hidden = false;
            _tween = _descriptionTextCanvasGroup.DOFade(1.0f, 0.8f);
            await _tween.Play()
                .WithCancellation(cancellationToken);
        }

        public void SetEnableCloseText(bool enable)
        {
            _closeText.enabled = enable;
        }

        public void SkipAnimation()
        {
            _iconList.PlayerResourceIconAnimation.SkipAnimation();

            _tween?.Kill(true);

            var isDescriptionTextHidden = string.IsNullOrEmpty(_descriptionText.Text);
            _descriptionTextObject.Hidden = isDescriptionTextHidden;
        }
    }
}
