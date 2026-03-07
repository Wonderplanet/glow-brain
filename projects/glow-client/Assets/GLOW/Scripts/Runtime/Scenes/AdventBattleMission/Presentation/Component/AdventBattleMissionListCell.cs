using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AdventBattleMission.Presentation.ViewModel;
using GLOW.Scenes.Mission.Presentation.Component;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.AdventBattleMission.Presentation.Component
{
    public class AdventBattleMissionListCell : UICollectionViewCell
    {
        [SerializeField] MissionProgressGaugeWithTextComponent _progressGauge;
        [SerializeField] PlayerResourceIconComponent _playerResourceIcon;
        [SerializeField] UIText _missionCriterionText;
        [SerializeField] UIObject _grayPlate;
        [SerializeField] Button _receiveButton;
        [SerializeField] Button _challengeButton;
        [SerializeField] Button _resourceIconButton;
        [SerializeField] UIImage _completeIcon;
        [SerializeField] UIText _limitedTermText;

        protected override void Awake()
        {
            base.Awake();

            AddButton(_challengeButton, "challenge");
            AddButton(_receiveButton, "receive");
            AddButton(_resourceIconButton, "resourceDetail");
        }

        public void SetupAdventBattleMissionCell(AdventBattleMissionCellViewModel cellViewModel)
        {
            SetupCell(
                cellViewModel.MissionStatus,
                cellViewModel.MissionProgress,
                cellViewModel.CriterionCount,
                cellViewModel.PlayerResourceIconViewModels,
                cellViewModel.MissionDescription);
            SetupLimitedTermText(cellViewModel.EndTimeSpan);
        }
        
        public void SetupLimitedTermText(RemainingTimeSpan remainingTimeSpan)
        {
            _limitedTermText.SetText(TimeSpanFormatter.FormatUntilEnd(remainingTimeSpan));
        }

        void SetupCell(
            MissionStatus missionStatus,
            MissionProgress missionProgress,
            CriterionCount criterionCount, 
            IReadOnlyList<PlayerResourceIconViewModel> playerResourceIconViewModels,
            MissionDescription missionDescription)
        {
            SetupFromMissionStatus(missionStatus);
            SetupProgressUi(missionProgress, criterionCount);
            SetupRewardUi(playerResourceIconViewModels);

            _missionCriterionText.SetText(missionDescription.Value);
        }

        void SetupFromMissionStatus(MissionStatus missionStatus)
        {
            switch (missionStatus)
            {
                case MissionStatus.Nothing:
                {
                    _challengeButton.gameObject.SetActive(true);
                    _receiveButton.gameObject.SetActive(false);
                    _grayPlate.Hidden = true;
                    _completeIcon.Hidden = true;
                }break;
                case MissionStatus.Receivable:
                {
                    _challengeButton.gameObject.SetActive(false);
                    _receiveButton.gameObject.SetActive(true);
                    _grayPlate.Hidden = true;
                    _completeIcon.Hidden = true;
                }break;
                case MissionStatus.Received:
                case MissionStatus.AllReceived:
                {
                    _challengeButton.gameObject.SetActive(false);
                    _receiveButton.gameObject.SetActive(false);
                    _grayPlate.Hidden = false;
                    _completeIcon.Hidden = false;
                }break;
                default:
                    throw new ArgumentOutOfRangeException(nameof(missionStatus), missionStatus, null);
            }
        }

        void SetupProgressUi(MissionProgress missionProgress, CriterionCount criterionCount)
        {
            if (criterionCount.IsZero())
            {
                _progressGauge.Hidden = true;
                return;
            }
            
            var rate = missionProgress / criterionCount;
            _progressGauge.SetProgressGaugeRate(rate);

            _progressGauge.SetProgressText(missionProgress.ToStringSeparated(), criterionCount.ToStringSeparated());
        }

        void SetupRewardUi(IReadOnlyList<PlayerResourceIconViewModel> playerResourceViewModels)
        {
            if (playerResourceViewModels.IsEmpty())
            {
                _playerResourceIcon.Hidden = true;
                return;
            }

            var playerResourceIconViewModel = playerResourceViewModels.First();
            _playerResourceIcon.Setup(playerResourceIconViewModel);
        }
    }
}