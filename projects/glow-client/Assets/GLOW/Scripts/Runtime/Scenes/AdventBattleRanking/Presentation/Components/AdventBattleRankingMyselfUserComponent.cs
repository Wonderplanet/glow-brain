using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.AdventBattleRanking.Presentation.Constants;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.AdventBattleRanking.Presentation.Components
{
    public class AdventBattleRankingMyselfUserComponent : UIComponent
    {
        [SerializeField] Image _unitIconImage;
        [SerializeField] Image _emblemImage;
        [SerializeField] UIText _rankingText;
        [SerializeField] UIText _userNameText;
        [SerializeField] UIText _userNameTextWithException;
        [SerializeField] UIText _scoreText;
        [SerializeField] UIObject _firstPlaceIcon;
        [SerializeField] UIObject _secondPlaceIcon;
        [SerializeField] UIObject _thirdPlaceIcon;
        [SerializeField] UIObject _notUpdateRanking;
        [SerializeField] UIObject _firstPlaceBackground;
        [SerializeField] UIObject _secondPlaceBackground;
        [SerializeField] UIObject _thirdPlaceBackground;
        [SerializeField] UIObject _lowerFourthPlaceBackground;
        [SerializeField] UIObject _scoreAreaNormal;
        [SerializeField] UIObject _scoreAreaEmpty;
        [SerializeField] UIObject _scoreAreaNotAchieve;
        [SerializeField] RankingRankIcon _rankingRankIcon;
        [SerializeField] UIObject _rankingRankIconBackground;

        public void SetUpUnitImage(CharacterIconAssetPath unitIconAssetPath)
        {
            _unitIconImage.gameObject.SetActive(!unitIconAssetPath.IsEmpty());
            SpriteLoaderUtil.Clear(_unitIconImage);

            if (unitIconAssetPath.IsEmpty())
            {
                return;
            }

            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_unitIconImage, unitIconAssetPath.Value);
        }

        public void SetUpEmblem(EmblemIconAssetPath emblemIconAssetPath)
        {
            _emblemImage.gameObject.SetActive(!emblemIconAssetPath.IsEmpty());
            SpriteLoaderUtil.Clear(_emblemImage);

            if (emblemIconAssetPath.IsEmpty())
            {
                return;
            }

            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_emblemImage, emblemIconAssetPath.Value);
        }

        public void SetUpRank(AdventBattleRankingRank rank, AdventBattleRankingCalculatingFlag calculatingRankings)
        {
            _notUpdateRanking.IsVisible = calculatingRankings;
            _rankingText.IsVisible = !calculatingRankings && (rank.IsEmpty() || rank.IsLowerFourth());
            _firstPlaceIcon.IsVisible = !calculatingRankings && rank.IsFirstRank();
            _secondPlaceIcon.IsVisible = !calculatingRankings && rank.IsSecondRank();
            _thirdPlaceIcon.IsVisible = !calculatingRankings && rank.IsThirdRank();

            SetUpBackground(rank);

            if (rank.IsLowerFourth())
            {
                _rankingText.SetText("{0} 位", rank.ToDisplayString());
            }
        }

        void SetUpBackground(AdventBattleRankingRank rank)
        {
            _firstPlaceBackground.IsVisible = rank.IsFirstRank();
            _secondPlaceBackground.IsVisible = rank.IsSecondRank();
            _thirdPlaceBackground.IsVisible = rank.IsThirdRank();
            _lowerFourthPlaceBackground.IsVisible = rank.IsEmpty() || rank.IsLowerFourth();
        }

        public void SetUpRankingViewStatus(AdventBattleRankingMyselfUserViewStatus viewStatus)
        {
            _scoreAreaNormal.IsVisible = viewStatus == AdventBattleRankingMyselfUserViewStatus.Normal;
            _scoreAreaEmpty.IsVisible = viewStatus == AdventBattleRankingMyselfUserViewStatus.EmptyCurrentRanking ||
                viewStatus == AdventBattleRankingMyselfUserViewStatus.ExcludeRanking;
            _scoreAreaNotAchieve.IsVisible = viewStatus == AdventBattleRankingMyselfUserViewStatus.NotAchieveRanking;

            // ViewStatusが Normal以外の場合は、背景を通常にする
            if (viewStatus != AdventBattleRankingMyselfUserViewStatus.Normal)
            {
                SetUpBackground(AdventBattleRankingRank.Empty);
            }

            // RankIconはNormal以外非表示にする
            _rankingRankIcon.IsVisible = viewStatus == AdventBattleRankingMyselfUserViewStatus.Normal;
            _rankingRankIconBackground.IsVisible = viewStatus == AdventBattleRankingMyselfUserViewStatus.Normal;

            // Normal以外はRankTextを---位にする
            if (viewStatus != AdventBattleRankingMyselfUserViewStatus.Normal)
            {
                _rankingText.SetText("---位");
            }

            _userNameText.IsVisible = viewStatus == AdventBattleRankingMyselfUserViewStatus.Normal;
            _userNameTextWithException.IsVisible = viewStatus != AdventBattleRankingMyselfUserViewStatus.Normal;
        }

        public void SetUpUserName(UserName userName)
        {
            _userNameText.SetText(userName.Value);
            _userNameTextWithException.SetText(userName.Value);
        }

        public void SetUpMaxScore(AdventBattleScore maxScore)
        {
            _scoreText.SetText(maxScore.ToDisplayString());
        }

        public void SetUpRankIcon(RankType rankType, AdventBattleScoreRankLevel rankLevel)
        {
            _rankingRankIcon.SetupRankType(rankType);
            _rankingRankIcon.PlayRankTierAnimation(rankLevel.ToScoreRankLevel());
        }
    }
}
