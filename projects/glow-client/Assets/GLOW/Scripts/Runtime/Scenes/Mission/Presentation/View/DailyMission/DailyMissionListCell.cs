using System;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.Mission.Presentation.Component;
using GLOW.Scenes.Mission.Presentation.ViewModel.DailyMission;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.Mission.Presentation.View.DailyMission
{
    public class DailyMissionListCell : UICollectionViewCell
    {
        [SerializeField] MissionProgressGaugeWithTextComponent _progressGauge;

        [SerializeField] MissionRewardAmountTextComponent _rewardAmountText;

        [SerializeField] UIText _missionCriterionText;

        [SerializeField] UIObject _grayPlate;

        [SerializeField] Button _receiveButton;

        [SerializeField] Button _challengeButton;
        
        [SerializeField] Button _missionBonusPointButton;

        [SerializeField] UIImage _completeIcon;

        [SerializeField] UIText _allAchievedText;

        protected override void Awake()
        {
            base.Awake();

            AddButton(_challengeButton, "challenge");
            AddButton(_receiveButton, "receive");
            AddButton(_missionBonusPointButton, "missionBonusPoint");
        }

        public void SetupDailyMissionCell(IDailyMissionCellViewModel viewModel)
        {
            SetupCell(viewModel.MissionStatus, viewModel.MissionProgress, viewModel.CriterionCount, viewModel.BonusPoint, viewModel.MissionDescription);
        }

        void SetupCell(MissionStatus missionStatus, MissionProgress missionProgress, CriterionCount criterionCount, BonusPoint bonusPoint, MissionDescription missionDescription)
        {
            SetupFromMissionStatus(missionStatus);
            SetupProgressUi(missionProgress, criterionCount);
            SetupRewardUi(bonusPoint);

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
                    _completeIcon.Hidden = true;
                    _grayPlate.Hidden = true;
                    _progressGauge.Hidden = false;
                    _allAchievedText.Hidden = true;
                }break;
                case MissionStatus.Receivable:
                {
                    _challengeButton.gameObject.SetActive(false);
                    _receiveButton.gameObject.SetActive(true);
                    _completeIcon.Hidden = true;
                    _grayPlate.Hidden = true;
                    _progressGauge.Hidden = false;
                    _allAchievedText.Hidden = true;
                }break;
                case MissionStatus.Received:
                {
                    _challengeButton.gameObject.SetActive(false);
                    _receiveButton.gameObject.SetActive(false);
                    _completeIcon.Hidden = false;
                    _grayPlate.Hidden = false;
                    _progressGauge.Hidden = false;
                    _allAchievedText.Hidden = true;
                }break;
                case MissionStatus.AllReceived:
                {
                    _challengeButton.gameObject.SetActive(false);
                    _receiveButton.gameObject.SetActive(false);
                    _completeIcon.Hidden = true;
                    _grayPlate.Hidden = false;
                    _progressGauge.Hidden = true;
                    _allAchievedText.Hidden = false;
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

        void SetupRewardUi(BonusPoint bonusPoint)
        {
            _rewardAmountText.SetAmountText(bonusPoint.ToStringSeparated());
        }
    }
}
