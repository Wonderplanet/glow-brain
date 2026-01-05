using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.PvpTop.Presentation.ViewModel;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.PvpTop.Presentation
{
    public class PvpTopOpponentComponent : UIObject
    {
        [Header("選択状態")]
        [SerializeField] GameObject _selectedObject;

        [Header("情報")]
        [SerializeField] UIImage _avatarIconImage;
        [SerializeField] UIImage _emblemIconImage;
        [SerializeField] UIText _userNameText;
        [SerializeField] UIText _victoryPointText;
        [SerializeField] UIText _totalPointText;
        [SerializeField] UIText _totalPartyStatus;
        [SerializeField] UIObject _totalPartyStatusUpperArrow;
        [Header("ランクアイコン")]
        [SerializeField] RankingRankIcon _pvpRankIcon;

        public void Setup(PvpTopOpponentViewModel viewModel)
        {
            //初期化
            _selectedObject.SetActive(false);

            SetUpAvatarIconImage(viewModel.CharacterIconAssetPath);
            SetUpEmblemIcon(viewModel.EmblemIconAssetPath);
            _userNameText.SetText(viewModel.UserName.Value);
            _victoryPointText.SetText("{0} pt", viewModel.Point.Value);
            _totalPointText.SetText("{0} pt", viewModel.TotalPoint.Value);

            // パーティ戦力
            _totalPartyStatus.SetText(viewModel.TotalPartyStatus.ToStringSeparated());
            _totalPartyStatusUpperArrow.IsVisible = viewModel.TotalPartyStatusUpperArrowFlag;

            // ランクアイコン
            _pvpRankIcon.IsVisible = true;
            _pvpRankIcon.SetupRankType(viewModel.PvpUserRankStatus.ToRankType());
            _pvpRankIcon.PlayRankTierAnimation(viewModel.PvpUserRankStatus.ToScoreRankLevel());
        }

        public void Select(bool isSelected)
        {
            _selectedObject.SetActive(isSelected);
        }

        public void SetUpAvatarIconImage(CharacterIconAssetPath unitIconAssetPath)
        {
            SpriteLoaderUtil.Clear(_avatarIconImage.Image);
            if (unitIconAssetPath.IsEmpty())
            {
                _avatarIconImage.IsVisible = false;
                return;
            }

            _avatarIconImage.IsVisible = true;
            UISpriteUtil.LoadSpriteWithFade(_avatarIconImage.Image, unitIconAssetPath.Value);
        }

        void SetUpEmblemIcon(EmblemIconAssetPath emblemIconAssetPath)
        {
            SpriteLoaderUtil.Clear(_emblemIconImage.Image);
            if (emblemIconAssetPath.IsEmpty())
            {
                _emblemIconImage.IsVisible = false;
                return;
            }

            _emblemIconImage.IsVisible = true;
            UISpriteUtil.LoadSpriteWithFade(_emblemIconImage.Image, emblemIconAssetPath.Value);
        }
    }
}
