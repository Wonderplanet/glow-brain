using System;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkPanelMission.Presentation.ViewModel;
using GLOW.Scenes.Mission.Presentation.Component;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.ArtworkPanelMission.Presentation.Component
{
    public class ArtworkPanelMissionListCell : UICollectionViewCell
    {
        [Header("ミッションのゲージと進捗数値表示")]
        [SerializeField] MissionProgressGaugeWithTextComponent _progressGauge;
        
        [Header("報酬アイコンとボタン(左:原画, 右:それ以外の報酬)")]
        [SerializeField] PlayerResourceIconComponent _playerResourceIconArtworkFragment;
        [SerializeField] PlayerResourceIconComponent _playerResourceIconOther;
        [SerializeField] Button _resourceIconButtonArtworkFragment;
        [SerializeField] Button _resourceIconButtonOther;
        
        [Header("ミッションの達成条件文")]
        [SerializeField] UIText _missionCriterionText;
        
        [Header("グレーアウト素材(全体用)")]
        [SerializeField] UIObject _grayPlate;
        
        [Header("達成済みアイコン素材")]
        [SerializeField] UIImage _completeIcon;
        
        [Header("受け取りボタン、挑戦するボタン")]
        [SerializeField] Button _receiveButton;
        [SerializeField] Button _challengeButton;
        
        protected override void Awake()
        {
            base.Awake();

            AddButton(_challengeButton, "challenge");
            AddButton(_receiveButton, "receive");
            AddButton(_resourceIconButtonArtworkFragment, "resourceDetailArtworkFragment");
            AddButton(_resourceIconButtonOther, "resourceDetailOther");
        }

        public void SetUpArtworkPanelMissionCell(ArtworkPanelMissionCellViewModel viewModel)
        {
            SetUpCell(
                viewModel.MissionStatus, 
                viewModel.MissionProgress, 
                viewModel.CriterionCount, 
                viewModel.ArtworkFragmentPlayerResourceIconViewModel,
                viewModel.OtherRewardPlayerResourceIconViewModel,
                viewModel.MissionDescription);
        }

        void SetUpCell(
            MissionStatus missionStatus, 
            MissionProgress missionProgress, 
            CriterionCount criterionCount, 
            PlayerResourceIconViewModel artworkFragmentPlayerResourceIconViewModel,
            PlayerResourceIconViewModel otherRewardPlayerResourceIconViewModel, 
            MissionDescription missionDescription)
        {
            SetUpFromMissionStatus(missionStatus);
            SetUpProgressUi(missionProgress, criterionCount);
            SetUpRewardUi(
                artworkFragmentPlayerResourceIconViewModel,
                otherRewardPlayerResourceIconViewModel);

            _missionCriterionText.SetText(missionDescription.Value);
        }

        void SetUpFromMissionStatus(MissionStatus missionStatus)
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

        void SetUpProgressUi(MissionProgress missionProgress, CriterionCount criterionCount)
        {
            var rate = (float)missionProgress.Value / criterionCount.Value;
            _progressGauge.SetProgressGaugeRate(rate);

            _progressGauge.SetProgressText(missionProgress.ToStringSeparated(), criterionCount.ToStringSeparated());
        }

        void SetUpRewardUi(
            PlayerResourceIconViewModel artworkFragmentPlayerResourceIconViewModel,
            PlayerResourceIconViewModel otherRewardPlayerResourceIconViewModel)
        {
            if (artworkFragmentPlayerResourceIconViewModel.IsEmpty())
            {
                _resourceIconButtonArtworkFragment.gameObject.SetActive(false);
            }
            else
            {
                _resourceIconButtonArtworkFragment.gameObject.SetActive(true);
                _playerResourceIconArtworkFragment.Setup(artworkFragmentPlayerResourceIconViewModel);
            }
            
            if (otherRewardPlayerResourceIconViewModel.IsEmpty())
            {
                _resourceIconButtonOther.gameObject.SetActive(false);
            }
            else
            {
                _resourceIconButtonOther.gameObject.SetActive(true);
                _playerResourceIconOther.Setup(otherRewardPlayerResourceIconViewModel);
            }
        }
    }
}