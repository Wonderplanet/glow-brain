using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.AdventBattleRanking.Presentation.Components
{
    public class AdventBattleRankingOtherUserCell : UICollectionViewCell
    {
        [SerializeField] Image _unitIconImage;
        [SerializeField] Image _emblemImage;
        [SerializeField] UIText _rankingText;
        [SerializeField] UIText _userNameText;
        [SerializeField] UIText _scoreText;
        [SerializeField] UIObject _myselfMark;
        [SerializeField] UIObject _firstPlaceIcon;
        [SerializeField] UIObject _secondPlaceIcon;
        [SerializeField] UIObject _thirdPlaceIcon;
        [SerializeField] UIObject _firstPlaceBackground;
        [SerializeField] UIObject _secondPlaceBackground;
        [SerializeField] UIObject _thirdPlaceBackground;
        [SerializeField] UIObject _lowerFourthPlaceBackground;
        [SerializeField] RankingRankIcon _rankingRankIcon;

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
            if (!emblemIconAssetPath.IsEmpty())
            {
                SpriteLoaderUtil.Clear(_emblemImage);
                UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_emblemImage, emblemIconAssetPath.Value);
            }
        }

        public void SetUpRank(AdventBattleRankingRank rank)
        {
            _rankingText.IsVisible = rank.IsLowerFourth();
            _firstPlaceIcon.IsVisible = rank.IsFirstRank();
            _secondPlaceIcon.IsVisible = rank.IsSecondRank();
            _thirdPlaceIcon.IsVisible = rank.IsThirdRank();

            if (rank.IsLowerFourth())
            {
                _rankingText.SetText("{0} 位", rank.ToDisplayString());
            }

            SetUpBackground(rank);
        }

        public void SetUpUserName(UserName userName)
        {
            _userNameText.SetText(userName.Value);
        }

        public void SetUpMaxScore(AdventBattleScore maxScore)
        {
            _scoreText.SetText(maxScore.ToDisplayString());
        }

        public void SetUpMyselfMark(bool isMyself)
        {
            _myselfMark.IsVisible = isMyself;
        }

        void SetUpBackground(AdventBattleRankingRank rank)
        {
            _firstPlaceBackground.IsVisible = rank.IsFirstRank();
            _secondPlaceBackground.IsVisible = rank.IsSecondRank();
            _thirdPlaceBackground.IsVisible = rank.IsThirdRank();
            _lowerFourthPlaceBackground.IsVisible = rank.IsEmpty() || rank.IsLowerFourth();
        }

        public void SetUpRankIcon(RankType rankType, AdventBattleScoreRankLevel rankLevel)
        {
            _rankingRankIcon.SetupRankType(rankType);
            _rankingRankIcon.PlayRankTierAnimation(rankLevel.ToScoreRankLevel());
        }
    }
}
