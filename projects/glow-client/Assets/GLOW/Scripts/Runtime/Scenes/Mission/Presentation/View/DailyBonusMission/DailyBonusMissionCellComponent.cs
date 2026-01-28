using System;
using System.Collections.Generic;
using System.Diagnostics.CodeAnalysis;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.Mission.Presentation.ViewModel.DailyBonus;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.Mission.Presentation.View.DailyBonusMission
{
    public class DailyBonusMissionCellComponent : UIComponent
    {
        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public struct MissionStatusImage
        {
            public MissionStatus Status;
            public Sprite StatusSprite;
        }

        [SerializeField] List<MissionStatusImage> _missionStatusImageList;
        [SerializeField] UIImage _missionStatusImage;
        [SerializeField] List<PlayerResourceIconComponent> _playerResourceIconComponents;
        [SerializeField] List<CanvasGroup> _playerResourceIconCanvasGroups;
        [SerializeField] UIText _dailyText;
        [SerializeField] UIImage _badgeIcon;
        [SerializeField] List<UIImage> _iconEffects;
        [SerializeField] List<DailyBonusMissionCompleteIconAnimationComponent> _completeCheckIcons;
        [SerializeField] List<Button> _playerResourceIconButtons;
        [SerializeField] UIObject _slashEffectObject;
        [SerializeField] UIImage _grayOutImage;

        public void SetUpDailyBonusMissionCell(
            IDailyBonusMissionCellViewModel viewModel, 
            Action<PlayerResourceIconViewModel> onRewardIconSelected)
        {
            var statusImage = _missionStatusImageList.Find(image => image.Status == viewModel.MissionStatus);
            _missionStatusImage.Sprite = statusImage.StatusSprite;

            SetUpDailyText(viewModel);
            SetUpRewardIconView(viewModel, onRewardIconSelected);
        }

        public async UniTask PlayDailyBonusStampAnimationAsync(
            CancellationToken cancellationToken)
        {
            var stampAnimationList = new List<UniTask>();
            for (var i = 0; i < _playerResourceIconButtons.Count; i++)
            {
                if(_playerResourceIconButtons[i].gameObject.activeSelf == false)
                {
                    continue;
                }
                
                var icon = _completeCheckIcons[i];
                icon.Hidden = false;
                stampAnimationList.Add(icon.PlayAnimationAsync(cancellationToken));
            }

            // 宝箱のアニメーションが全部終わるまで待つ
            await UniTask.WhenAll(stampAnimationList);
        }

        void SetUpDailyText(IDailyBonusMissionCellViewModel viewModel)
        {
            _dailyText.SetText(viewModel.LoginDayCount.ToLoginDayCountText());
        }

        void SetUpRewardIconView(IDailyBonusMissionCellViewModel viewModel, Action<PlayerResourceIconViewModel> onRewardIconSelected)
        {
            for (var i = 0; i < viewModel.PlayerResourceIconViewModels.Count; i++)
            {
                if(i >= _playerResourceIconComponents.Count || i >= _playerResourceIconCanvasGroups.Count || i >= _completeCheckIcons.Count || i >= _playerResourceIconButtons.Count)
                {
                    throw new ArgumentOutOfRangeException();
                }
                
                var playerResourceIconComponent = _playerResourceIconComponents[i];
                var playerResourceIconCanvasGroup = _playerResourceIconCanvasGroups[i];
                var completeCheckIcon = _completeCheckIcons[i];
                var playerResourceIconButton = _playerResourceIconButtons[i];
                var playerResourceIconViewModel = viewModel.PlayerResourceIconViewModels[i];
                var iconEffect= _iconEffects[i];
                playerResourceIconComponent.Setup(playerResourceIconViewModel);
                playerResourceIconButton.onClick.RemoveAllListeners();
                playerResourceIconButton.onClick.AddListener(() =>
                {
                    switch (viewModel.MissionStatus)
                    {
                        case MissionStatus.Nothing:
                        case MissionStatus.Received:
                            onRewardIconSelected?.Invoke(playerResourceIconViewModel);
                            break;
                        case MissionStatus.Receivable:
                            break;
                    }
                });
                SetUpDailyBonusUiVisibleFromMissionStatus(
                    viewModel.MissionStatus, 
                    completeCheckIcon, 
                    iconEffect,
                    playerResourceIconCanvasGroup);
            }
            
            for (var i = viewModel.PlayerResourceIconViewModels.Count; i < _playerResourceIconComponents.Count; i++)
            {
                // 未使用のアイコンを非表示にする
                var playerResourceIconButton = _playerResourceIconButtons[i];
                playerResourceIconButton.gameObject.SetActive(false);
            }
            
            SetUpDailyBonusCellCommonUiVisibleFromMissionStatus(viewModel.MissionStatus);
        }

        void SetUpDailyBonusUiVisibleFromMissionStatus(
            MissionStatus missionStatus, 
            DailyBonusMissionCompleteIconAnimationComponent completeCheckIcon,
            UIImage iconEffect,
            CanvasGroup playerResourceIconCanvasGroup)
        {
            switch (missionStatus)
            {
                case MissionStatus.Nothing:
                {
                    iconEffect.Hidden = true;
                    completeCheckIcon.Hidden = true;
                    playerResourceIconCanvasGroup.alpha = 1.0f;
                }break;
                case MissionStatus.Receivable:
                {
                    iconEffect.Hidden = false;
                    completeCheckIcon.Hidden = true;
                    playerResourceIconCanvasGroup.alpha = 1.0f;
                }break;
                case MissionStatus.Received:
                {
                    iconEffect.Hidden = true;
                    completeCheckIcon.Hidden = false;
                    completeCheckIcon.PlayDefAnimation();
                    playerResourceIconCanvasGroup.alpha = 0.5f;
                }break;
                default:
                    throw new ArgumentOutOfRangeException();
            }
        }

        void SetUpDailyBonusCellCommonUiVisibleFromMissionStatus(MissionStatus missionStatus)
        {
            _slashEffectObject.Hidden = missionStatus != MissionStatus.Receivable;
            _grayOutImage.Hidden = missionStatus != MissionStatus.Received;
        }
    }
}
