using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.AdventBattleRewardList.Presentation.Component
{
    public class AdventBattlePersonalScoreRewardComponent : UIObject
    {
        [SerializeField] UIText _rankNameText;
        [SerializeField] UIText _rankingNumberText;
        [SerializeField] RankingRankIcon _rankingRankIcon;
        [SerializeField] UIText _rankLowerScoreText;
        [SerializeField] UIObject _rankRequiredLowerScoreObject;
        [SerializeField] UIImage _rankBackgroundImage;

        public void SetRankRequiredScore(AdventBattleScore score)
        {
            _rankingNumberText.IsVisible = false;
            _rankRequiredLowerScoreObject.IsVisible = true;
            _rankLowerScoreText.SetText("{0} ~", score.ToDisplayString());
        }

        public void HideScore()
        {
            _rankRequiredLowerScoreObject.IsVisible = false;
        }

        public void SetRankName(RankType rankType, AdventBattleScoreRankLevel rankLevel)
        {
            _rankNameText.IsVisible = true;
            _rankNameText.SetText(rankType.ToDisplayStringWithRankLevel(rankLevel));
        }

        public void SetRankingNumber(AdventBattleRankingRank rankUpper, AdventBattleRankingRank rankLower)
        {
            _rankingNumberText.IsVisible = true;

            if (rankUpper.IsEmpty())
            {
                _rankingNumberText.SetText(ZString.Format("{0} 位", rankLower.ToDisplayString()));
            }
            else if (rankLower.IsInfinity())
            {
                _rankingNumberText.SetText(ZString.Format("{0} 位 ~ ", rankUpper.ToDisplayString()));
            }
            else
            {
                _rankingNumberText.SetText(
                    ZString.Format(
                        "{0} 位 ~ {1} 位", 
                        rankUpper.ToDisplayString(), 
                        rankLower.ToDisplayString()));
            }

            _rankNameText.IsVisible = true;
        }

        public void SetUpRankingNumberVisible(AdventBattleRankingRank rankingRank)
        {
            _rankingNumberText.IsVisible = rankingRank.IsLowerFourth();
        }

        public void SetRankIconType(RankType rankType)
        {
            _rankingRankIcon.IsVisible = true;
            _rankBackgroundImage.IsVisible = true;
            _rankingRankIcon.SetupRankType(rankType);
        }

        public void PlayRankTierAnimation(AdventBattleScoreRankLevel rankLevel)
        {
            if (rankLevel.IsEmpty())
            {
                return;
            }

            _rankingRankIcon.IsVisible = true;
            _rankingRankIcon.PlayRankTierAnimation(rankLevel.ToScoreRankLevel());
        }

        public void HideRankIcon()
        {
            _rankingRankIcon.IsVisible = false;
        }
    }
}
