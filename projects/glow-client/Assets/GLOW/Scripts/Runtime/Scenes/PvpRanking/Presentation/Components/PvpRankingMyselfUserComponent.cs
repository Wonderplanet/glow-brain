using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.PvpRanking.Domain.ValueObjects;
using GLOW.Scenes.PvpRanking.Presentation.Constants;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.PvpRanking.Presentation.Components
{
    public class PvpRankingMyselfUserComponent : UIComponent
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
        [SerializeField] RankingRankIcon _pvpRankIcon;
        [SerializeField] UIObject _pvpRankIconBackground;
        [SerializeField] UIObject _pointAreaNormal;
        [SerializeField] UIObject _pointAreaEmpty;
        [SerializeField] UIObject _pointAreaNotAchieve;

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

        public void SetUpRank(PvpRankingRank rank, PvpRankingCalculatingFlag calculatingRankings)
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

        void SetUpBackground(PvpRankingRank rank)
        {
            _firstPlaceBackground.IsVisible = rank.IsFirstRank();
            _secondPlaceBackground.IsVisible = rank.IsSecondRank();
            _thirdPlaceBackground.IsVisible = rank.IsThirdRank();
            _lowerFourthPlaceBackground.IsVisible = rank.IsEmpty() || rank.IsLowerFourth();
        }

        public void SetUpRankingViewStatus(PvpRankingMyselfUserViewStatus viewStatus)
        {
            _pointAreaNormal.IsVisible = viewStatus == PvpRankingMyselfUserViewStatus.Normal;
            _pointAreaEmpty.IsVisible = viewStatus == PvpRankingMyselfUserViewStatus.EmptyCurrentRanking ||
                viewStatus == PvpRankingMyselfUserViewStatus.EmptyPrevRanking ||
                viewStatus == PvpRankingMyselfUserViewStatus.ExcludeRanking;
            _pointAreaNotAchieve.IsVisible = viewStatus == PvpRankingMyselfUserViewStatus.NotAchieveRanking;

            // ViewStatusが Normal以外の場合は、背景を通常にする
            if (viewStatus != PvpRankingMyselfUserViewStatus.Normal)
            {
                SetUpBackground(PvpRankingRank.Empty);
            }

            // RankIconはNormal以外非表示にする
            _pvpRankIcon.IsVisible = viewStatus == PvpRankingMyselfUserViewStatus.Normal;
            _pvpRankIconBackground.IsVisible = viewStatus == PvpRankingMyselfUserViewStatus.Normal;

            // Normal以外はRankTextを---位にする
            if (viewStatus != PvpRankingMyselfUserViewStatus.Normal)
            {
                _rankingText.SetText("---位");
            }

            _userNameText.IsVisible = viewStatus == PvpRankingMyselfUserViewStatus.Normal;
            _userNameTextWithException.IsVisible = viewStatus != PvpRankingMyselfUserViewStatus.Normal;
        }

        public void SetUpUserName(UserName userName)
        {
            _userNameText.SetText(userName.Value);
            _userNameTextWithException.SetText(userName.Value);
        }

        public void SetUpScore(PvpPoint totalPoint)
        {
            _scoreText.SetText(totalPoint.ToDisplayString());
        }

        public void SetUpPvpRankIcon(PvpUserRankStatus pvpUserRankStatus)
        {
            _pvpRankIcon.SetupRankType(pvpUserRankStatus.ToRankType());
            _pvpRankIcon.PlayRankTierAnimation(pvpUserRankStatus.ToScoreRankLevel());
        }
    }
}
