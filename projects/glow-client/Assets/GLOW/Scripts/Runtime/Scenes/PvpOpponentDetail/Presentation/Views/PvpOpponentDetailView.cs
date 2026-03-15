using System.Collections.Generic;
using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using GLOW.Scenes.PvpTop.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using UnityEngine.Serialization;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.PvpOpponentDetail.Presentation.Views
{
    public enum PvpOpponentDetailTabType
    {
        Unit,
        Artwork
    }

    public class PvpOpponentDetailView : UIView
    {
        [SerializeField] Image _unitIcon;
        [SerializeField] Image _emblemImage;
        [SerializeField] UIText _victoryPointText;
        [SerializeField] UIText _userNameText;
        [SerializeField] UIText _totalPointText;
        [SerializeField] UIText _totalPartyStatusText;
        [SerializeField] UIText _tabTypeText;
        [SerializeField] UIObject _totalPartyStatusUpperArrow;
        [SerializeField] PvpOpponentDetailUnitIcon _unitIconPrefab;
        [SerializeField] GameObject _unitIconParent;
        [SerializeField] PvpOpponentDetailArtworkIcon _artworkIconPrefab;
        [SerializeField] GameObject _artworkIconParent;
        [SerializeField] RankingRankIcon _pvpRankIcon;
        [SerializeField] ChildScaler _playerInfoChildScaler;
        [SerializeField] ChildScaler _partyChildScaler;
        [SerializeField] ChildScaler _artworkChildScaler;
        [SerializeField] UIToggleableComponentGroup _toggleableTab;

        public void SetUpUnitIcon(CharacterIconAssetPath unitIconAssetPath)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_unitIcon, unitIconAssetPath.Value);
        }

        public void SetUpEmblem(EmblemIconAssetPath emblemIconAssetPath)
        {
            var isEmblemIconEmpty = emblemIconAssetPath.IsEmpty();
            _emblemImage.gameObject.SetActive(!isEmblemIconEmpty);
            if (!isEmblemIconEmpty)
            {
                SpriteLoaderUtil.Clear(_emblemImage);
                UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_emblemImage, emblemIconAssetPath.Value);
            }
        }

        public void SetUpVictoryPoint(PvpPoint point)
        {
            _victoryPointText.SetText(ZString.Format("+{0}", point.ToDisplayString()));
        }

        public void SetUpUserName(UserName userName)
        {
            _userNameText.SetText(userName.Value);
        }

        public void SetUpTotalPoint(PvpPoint totalPoint)
        {
            _totalPointText.SetText(totalPoint.ToDisplayString());
        }

        public void SetUpTotalPartyStatus(TotalPartyStatus totalPartyStatus)
        {
            _totalPartyStatusText.SetText(totalPartyStatus.ToStringSeparated());
        }

        public void SetUpTotalPartyStatusUpperArrowFlag(bool isUpperArrowVisible)
        {
            _totalPartyStatusUpperArrow.IsVisible = isUpperArrowVisible;
        }

        public void SetUpUnitIcons(IReadOnlyList<PvpTopOpponentPartyUnitViewModel> unitIcons)
        {
            foreach (Transform child in _unitIconParent.transform)
            {
                Destroy(child.gameObject);
            }

            foreach (var icon in unitIcons)
            {
                var unitIcon = Instantiate(_unitIconPrefab, _unitIconParent.transform);
                unitIcon.SetUpUnitIcon(icon.UnitIconAssetPath);
                unitIcon.SetUpRoleIcon(icon.RoleType);
                unitIcon.SetUpColorIcon(icon.Color);
                unitIcon.SetUpRarityFrame(icon.Rarity);
                unitIcon.SetUpLevel(icon.Level);
                unitIcon.SetUpGrade(icon.Grade);
            }
        }

        public void SetUpArtworkIcons(IReadOnlyList<PvpOpponentArtworkPartyViewModel> artworkIcons)
        {
            foreach (Transform child in _artworkIconParent.transform)
            {
                Destroy(child.gameObject);
            }

            foreach (var artwork in artworkIcons)
            {
                var artworkIcon = Instantiate(_artworkIconPrefab, _artworkIconParent.transform);
                artworkIcon.SetArtworkImage(artwork.ArtworkAssetPath);
                artworkIcon.SetRarityImage(artwork.Rarity);
                artworkIcon.SetGrade(artwork.Grade);
            }
        }

        public void SetUpPvpRankIcon(PvpUserRankStatus pvpUserRankStatus)
        {
            _pvpRankIcon.SetupRankType(pvpUserRankStatus.ToRankType());
            _pvpRankIcon.PlayRankTierAnimation(pvpUserRankStatus.ToScoreRankLevel());
        }

        public void PlayPlayerInfoAppearanceAnimation()
        {
            _playerInfoChildScaler.Play();
        }

        public void SetTabInteractable(PvpOpponentDetailTabType tabType)
        {
            _tabTypeText.SetText(
                tabType == PvpOpponentDetailTabType.Unit
                    ? "パーティ編成"
                    : "原画編成"
            );

            _toggleableTab.SetToggleOn(tabType.ToString());
            _unitIconParent.SetActive(tabType == PvpOpponentDetailTabType.Unit);
            _artworkIconParent.SetActive(tabType == PvpOpponentDetailTabType.Artwork);

            if (tabType == PvpOpponentDetailTabType.Unit) _partyChildScaler.Play();
            else _artworkChildScaler.Play();
        }
    }
}
