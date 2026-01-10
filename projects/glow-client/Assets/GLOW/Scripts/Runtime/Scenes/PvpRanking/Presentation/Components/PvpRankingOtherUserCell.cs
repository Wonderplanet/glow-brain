using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.PvpRanking.Domain.ValueObjects;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.PvpRanking.Presentation.Components
{
    public class PvpRankingOtherUserCell : UICollectionViewCell
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
        [SerializeField] RankingRankIcon _pvpRankIcon;

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

        public void SetUpRank(PvpRankingRank rank)
        {
            _rankingText.gameObject.SetActive(rank.IsLowerFourth());
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

        public void SetUpScore(PvpPoint totalPoint)
        {
            _scoreText.SetText(totalPoint.ToDisplayString());
        }

        public void SetUpMyselfMark(PvpRankingMyselfFlag isMyself)
        {
            _myselfMark.IsVisible = isMyself;
        }

        void SetUpBackground(PvpRankingRank rank)
        {
            _firstPlaceBackground.IsVisible = rank.IsFirstRank();
            _secondPlaceBackground.IsVisible = rank.IsSecondRank();
            _thirdPlaceBackground.IsVisible = rank.IsThirdRank();
            _lowerFourthPlaceBackground.IsVisible = rank.IsEmpty() || rank.IsLowerFourth();
        }

        public void SetUpPvpRankIcon(PvpUserRankStatus pvpUserRankStatus)
        {
            _pvpRankIcon.SetupRankType(pvpUserRankStatus.ToRankType());
            _pvpRankIcon.PlayRankTierAnimation(pvpUserRankStatus.ToScoreRankLevel());
        }
    }
}
