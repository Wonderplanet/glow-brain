using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Views.UIAnimator;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Presentation.Components;
using GLOW.Scenes.ArtworkFragment.Presentation.ViewModels;
using GLOW.Scenes.ArtworkPanelMission.Domain.ValueObject;
using GLOW.Scenes.ArtworkPanelMission.Presentation.Component;
using GLOW.Scenes.ArtworkPanelMission.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.ArtworkPanelMission.Presentation.View
{
    public class ArtworkPanelMissionView : UIView
    {
        [Header("残り時間表示")]
        [SerializeField] UIText _remainingTimeText;
        
        [Header("原画パネル本体")]
        [SerializeField] ArtworkPanelComponent _artworkPanelComponent;
        
        [Header("背景画像")]
        [SerializeField] UIImage _artworkThumbnailBackGroundImage;
        [SerializeField] UILightBlurTextureComponent _uiLightBlurTextureComponent;
        
        [Header("コンプリート時に表示する文字関連のAnimator")]
        [SerializeField] UIObject _completeLabelObject;
        [SerializeField] Animator _completeLabelAnimator;
        
        [Header("パネルミッションクリア数内訳(クリア数/総数)")]
        [SerializeField] UIText _panelMissionFractionText;
        
        [Header("ミッション一覧リスト")]
        [SerializeField] ArtworkPanelMissionListComponent _artworkPanelMissionListComponent;

        [Header("一括受け取りボタン")]
        [SerializeField] Button _bulkReceiveButton;
        
        [Header("スキップボタン")]
        [SerializeField] UIObject _skipButtonObj;
        
        [Header("原画テクスチャアニメーター")]
        [SerializeField] UIAnimator _artworkTextureAnimator;

        const string CompleteInAnimation = "PanelMission-Complete-in";

        public void SetUpArtworkPanelComponent(ArtworkPanelViewModel artworkPanelViewModel)
        {
            _artworkPanelComponent.Setup(artworkPanelViewModel);
        }
        
        public void SetUpArtworkBackgroundImage(ArtworkPanelViewModel artworkPanelViewModel)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _artworkThumbnailBackGroundImage.Image,
                artworkPanelViewModel.FragmentPanelViewModel.AssetPath.Value,
                () =>
                {
                    if (!_uiLightBlurTextureComponent || !_artworkThumbnailBackGroundImage)
                    {
                        return;
                    }
                    
                    _uiLightBlurTextureComponent.IsVisible = true;
                    _uiLightBlurTextureComponent.SetTexture(_artworkThumbnailBackGroundImage.Image.sprite.texture);
                    _artworkThumbnailBackGroundImage.IsVisible = false;
                });
        }
        
        public void InitializeMissionListView(
            IUICollectionViewDataSource collectionViewDataSource,
            IUICollectionViewDelegate collectionViewDelegate)
        {
            _artworkPanelMissionListComponent.Initialize(
                collectionViewDataSource,
                collectionViewDelegate);
        }
        
        public ArtworkPanelMissionListCell DequeueReusableCell()
        {
            return _artworkPanelMissionListComponent.DequeueReusableCell();
        }
        
        public void SetUpMissionListView(
            IReadOnlyList<ArtworkPanelMissionCellViewModel> cellViewModels)
        {
            _artworkPanelMissionListComponent.SetUpMissionList(cellViewModels);
        }
        
        public void SetUpMissionFractionText(
            ArtworkPanelMissionCount achievedCount,
            ArtworkPanelMissionCount totalCount)
        {
            _panelMissionFractionText.SetText(
                ZString.Format("{0}/{1}", achievedCount.Value, totalCount.Value));
        }
        
        public void SetRemainingTimeText(RemainingTimeSpan remainingTimeSpan)
        {
            _remainingTimeText.SetText(TimeSpanFormatter.FormatUntilEnd(remainingTimeSpan));
        }
        
        public void SetUpCompleteLabelVisible(ArtworkPanelMissionCount achievedCount, ArtworkPanelMissionCount totalCount)
        {
            _completeLabelObject.IsVisible = achievedCount == totalCount;
        }
        
        public void BulkReceiveButtonInteractable(bool interactable)
        {
            _bulkReceiveButton.interactable = interactable;
        }
        
        public async UniTask PlayArtworkFragmentAnimation(
            IReadOnlyList<ArtworkFragmentPositionNum> positions, 
            CancellationToken cancellationToken)
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
            await _artworkPanelComponent.PlayArtworkCompleteAnimation(addHp, cancellationToken);
        }
        
        public void PlayCompleteLabelInAnimation()
        {
            _completeLabelObject.IsVisible = true;
            _completeLabelAnimator.Play(CompleteInAnimation);
        }

        public void SkipArtworkCompleteAnimation()
        {
            _artworkPanelComponent.SkipArtworkCompleteAnimation();
        }

        public void SetSkipButtonVisible(bool visible)
        {
            _skipButtonObj.IsVisible = visible;
        }

        public void SetArtworkTextureAnimatorEnable(bool enable)
        {
            _artworkTextureAnimator.enabled = enable;
        }
    }
}