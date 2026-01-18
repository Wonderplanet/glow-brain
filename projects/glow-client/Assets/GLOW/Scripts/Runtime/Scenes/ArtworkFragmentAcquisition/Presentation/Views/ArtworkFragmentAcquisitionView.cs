using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Presentation.Components;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.Views
{
    /// <summary>
    /// 41_メインクエスト・ステージ
    /// 　41-2_ステージ共通
    /// 　　41-2-10_原画のかけら獲得ダイアログ
    /// </summary>
    public class ArtworkFragmentAcquisitionView : UIView
    {
        [SerializeField] ArtworkPanelComponent _artworkPanelComponent;
        [SerializeField] Animator _underAnimator;
        [SerializeField] UIText _seriesNameText;
        [SerializeField] UIText _descriptionText;
        [SerializeField] Button _skipButton;
        [SerializeField] Animator _closeButton;

        static readonly int IsFade = Animator.StringToHash("IsFade");
        const string CloseButtonAnimationKey = "Btn_PicturePieceDialog";

        public void Setup(ArtworkFragmentAcquisitionViewModel viewModel)
        {
            _closeButton.enabled = false;
            _closeButton.gameObject.GetComponent<CanvasGroup>().alpha = 0;

            _artworkPanelComponent.Setup(viewModel.ArtworkPanelViewModel);
            _seriesNameText.SetText(viewModel.ArtworkName.Value);
            _descriptionText.SetText(viewModel.Description.Value);
        }

        public void SetSkipButtonAction(Action action)
        {
            _skipButton.onClick.AddListener(() => action?.Invoke());
            _skipButton.gameObject.SetActive(true);
        }

        public async UniTask PlayArtworkFragmentAnimation(IReadOnlyList<ArtworkFragmentPositionNum> positions, CancellationToken cancellationToken)
        {
            await _artworkPanelComponent.PlayArtworkFragmentAnimation(positions, cancellationToken);

            await UniTask.Delay(TimeSpan.FromSeconds(1.0f), cancellationToken: cancellationToken);
        }

        public void SkipArtworkFragmentAnimation(IReadOnlyList<ArtworkFragmentPositionNum> positions)
        {
            _artworkPanelComponent.SkipArtworkFragmentAnimation(positions);
        }

        public async UniTask PlayArtworkCompleteAnimation(HP addHp, CancellationToken cancellationToken)
        {
            _underAnimator.SetTrigger(IsFade);
            await _artworkPanelComponent.PlayArtworkCompleteAnimation(addHp, cancellationToken);
        }

        public void SkipArtworkCompleteAnimation()
        {
            _artworkPanelComponent.SkipArtworkCompleteAnimation();
            _underAnimator.Play("LockOpened");
        }

        public void EndAnimation()
        {
            _closeButton.enabled = true;
            _closeButton.Play(CloseButtonAnimationKey);
            _skipButton.gameObject.SetActive(false);
        }
    }
}
