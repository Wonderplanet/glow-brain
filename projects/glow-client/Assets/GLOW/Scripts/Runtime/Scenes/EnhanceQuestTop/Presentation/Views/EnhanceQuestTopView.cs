using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.EnhanceQuestTop.Presentation.Views
{
    /// <summary>
    /// 45_強化クエスト
    /// 　42-5_1日N回強化クエスト
    /// 　　42-5-2_ 1日N回コイン獲得クエストTOP画面
    /// </summary>
    public class EnhanceQuestTopView : UIView
    {
        [SerializeField] EnhanceQuestTopStartButtonComponent _startButton;
        [SerializeField] UIText _partyNameText;
        [SerializeField] UIText _nextBonusScoreText;
        [SerializeField] GameObject _nextBonusRoot;
        [SerializeField] UIText _bonusText;
        [SerializeField] UIObject _bonusRoot;
        [SerializeField] EnhanceQuestTopHighScoreComponent _highScoreComponent;
        [SerializeField] CampaignBalloonMultiSwitcherComponent _campaignBalloonMultiSwitcherComponent;

        public void Setup(EnhanceQuestTopViewModel viewModel)
        {
            _startButton.Setup(viewModel.ChallengeCount, viewModel.AdChallengeCount, viewModel.HeldAdSkipPassInfoViewModel);
            _nextBonusScoreText.SetText("{0} pt", viewModel.NextThresholdScore);
            _nextBonusRoot.SetActive(!viewModel.NextThresholdScore.IsEmpty());
            _highScoreComponent.Setup(viewModel.HighScore, viewModel.NextThresholdScore, viewModel.NextThresholdRewardAmount);

            _campaignBalloonMultiSwitcherComponent.SetUpCampaignBalloons(viewModel.CampaignViewModels);
            
            // パーティー
            UpdatePartyName(viewModel.PartyName);
            UpdateBonus(viewModel.TotalBonusPercentage);
        }

        public void UpdatePartyName(PartyName partyName)
        {
            _partyNameText.SetText(partyName.Value);
        }

        public void UpdateBonus(EventBonusPercentage totalBonusPercentage)
        {
            _bonusText.SetText("{0}倍!!", totalBonusPercentage.ToStringRatio());
            _bonusRoot.Hidden = totalBonusPercentage.IsEmpty();
        }
    }
}
