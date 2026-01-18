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
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.PvpOpponentDetail.Presentation.Views
{
    public class PvpOpponentDetailView : UIView
    {
        [SerializeField] Image _unitIcon;
        [SerializeField] Image _emblemImage;
        [SerializeField] UIText _victoryPointText;
        [SerializeField] UIText _userNameText;
        [SerializeField] UIText _totalPointText;
        [SerializeField] UIText _totalPartyStatusText;
        [SerializeField] UIObject _totalPartyStatusUpperArrow;
        [SerializeField] PvpOpponentDetailUnitIcon _unitIconPrefab;
        [SerializeField] GameObject _unitIconParent;
        [SerializeField] RankingRankIcon _pvpRankIcon;
        [SerializeField] ChildScaler _playerInfoChildScaler;
        [SerializeField] ChildScaler _partyChildScaler;

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

            _partyChildScaler.Play();
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
    }
}
