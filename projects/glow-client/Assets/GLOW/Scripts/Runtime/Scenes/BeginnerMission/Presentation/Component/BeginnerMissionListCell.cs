using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.BeginnerMission.Domain.ValueObject;
using GLOW.Scenes.BeginnerMission.Presentation.ViewModel;
using GLOW.Scenes.Mission.Presentation.Component;
using ModestTree;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.BeginnerMission.Presentation.Component
{
    public class BeginnerMissionListCell : UICollectionViewCell
    {
        [SerializeField] MissionProgressGaugeWithTextComponent _progressGauge;
        [SerializeField] PlayerResourceIconComponent _playerResourceIconLeft;
        [SerializeField] PlayerResourceIconComponent _playerResourceIconRight;
        [SerializeField] MissionRewardAmountTextComponent _rewardAmountText;
        [SerializeField] UIText _missionCriterionText;
        [SerializeField] UIObject _grayPlate;
        [SerializeField] Button _receiveButton;
        [SerializeField] Button _challengeButton;
        [SerializeField] UIObject _receiveButtonGrayOutObject;
        [SerializeField] UIObject _challengeButtonGrayOutObject;
        [SerializeField] Button _resourceIconButtonLeft;
        [SerializeField] Button _resourceIconButtonRight;
        [SerializeField] Button _missionBonusPointButton;
        [SerializeField] UIImage _completeIcon;

        protected override void Awake()
        {
            base.Awake();

            AddButton(_challengeButton, "challenge");
            AddButton(_receiveButton, "receive");
            AddButton(_resourceIconButtonLeft, "resourceDetailLeft");
            AddButton(_resourceIconButtonRight, "resourceDetailRight");
            AddButton(_missionBonusPointButton, "missionBonusPoint");
        }

        public void SetupBeginnerMissionCell(IBeginnerMissionCellViewModel cellViewModel)
        {
            SetupCell(
                cellViewModel.MissionStatus, 
                cellViewModel.MissionProgress, 
                cellViewModel.CriterionCount, 
                cellViewModel.PlayerResourceIconViewModels, 
                cellViewModel.BonusPoint, 
                cellViewModel.IsLock,
                cellViewModel.MissionDescription);
        }

        void SetupCell(
            MissionStatus missionStatus, 
            MissionProgress missionProgress, 
            CriterionCount criterionCount, 
            IReadOnlyList<PlayerResourceIconViewModel> playerResourceIconViewModels, 
            BonusPoint bonusPoint, 
            BeginnerMissionLockFlag isLock,
            MissionDescription missionDescription)
        {
            SetupFromMissionStatus(missionStatus, isLock);
            SetupProgressUi(missionProgress, criterionCount);
            SetupRewardUi(playerResourceIconViewModels);
            SetupBonusPointAmount(bonusPoint);

            _missionCriterionText.SetText(missionDescription.Value);
        }

        void SetupFromMissionStatus(MissionStatus missionStatus, BeginnerMissionLockFlag isLock)
        {
            switch (missionStatus)
            {
                case MissionStatus.Nothing:
                {
                    _challengeButton.gameObject.SetActive(true);
                    _receiveButton.gameObject.SetActive(false);
                    _challengeButtonGrayOutObject.IsVisible = isLock;
                    _receiveButtonGrayOutObject.IsVisible = false;
                    _grayPlate.Hidden = true;
                    _completeIcon.Hidden = true;
                }break;
                case MissionStatus.Receivable:
                {
                    _challengeButton.gameObject.SetActive(false);
                    _receiveButton.gameObject.SetActive(true);
                    _challengeButtonGrayOutObject.IsVisible = false;
                    _receiveButtonGrayOutObject.IsVisible = isLock;
                    _grayPlate.Hidden = true;
                    _completeIcon.Hidden = true;
                }break;
                case MissionStatus.Received:
                case MissionStatus.AllReceived:
                {
                    _challengeButton.gameObject.SetActive(false);
                    _receiveButton.gameObject.SetActive(false);
                    _challengeButtonGrayOutObject.IsVisible = false;
                    _receiveButtonGrayOutObject.IsVisible = false;
                    _grayPlate.Hidden = false;
                    _completeIcon.Hidden = false;
                }break;
                default:
                    throw new ArgumentOutOfRangeException(nameof(missionStatus), missionStatus, null);
            }
        }

        void SetupProgressUi(MissionProgress missionProgress, CriterionCount criterionCount)
        {
            var rate = (float)missionProgress.Value / criterionCount.Value;
            _progressGauge.SetProgressGaugeRate(rate);

            _progressGauge.SetProgressText(missionProgress.ToStringSeparated(), criterionCount.ToStringSeparated());
        }

        void SetupRewardUi(IReadOnlyList<PlayerResourceIconViewModel> playerResourceViewModels)
        {
            if (playerResourceViewModels.IsEmpty())
            {
                _resourceIconButtonLeft.gameObject.SetActive(false);
                _resourceIconButtonRight.gameObject.SetActive(false);
                return;
            }

            if (playerResourceViewModels.Count == 1)
            {
                var playerResourceIconViewModel = playerResourceViewModels.First();
                _playerResourceIconLeft.Setup(playerResourceIconViewModel);
                _resourceIconButtonLeft.gameObject.SetActive(true);
                
                _resourceIconButtonRight.gameObject.SetActive(false);

                return;
            }

            var playerResourceIconViewModelLeft = playerResourceViewModels.First();
            _playerResourceIconLeft.Setup(playerResourceIconViewModelLeft);
            _resourceIconButtonLeft.gameObject.SetActive(true);
            
            var playerResourceIconViewModelRight = playerResourceViewModels[1];
            _playerResourceIconRight.Setup(playerResourceIconViewModelRight);
            _resourceIconButtonRight.gameObject.SetActive(true);
        }

        void SetupBonusPointAmount(BonusPoint bonusPoint)
        {
            _rewardAmountText.SetAmountText(bonusPoint.ToStringSeparated());
        }
    }
}