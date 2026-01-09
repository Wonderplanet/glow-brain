using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.UnitEnhance.Presentation.Views.Components
{
    public class UnitEnhanceUnitInfoComponent : MonoBehaviour
    {
        [Serializable]
        public struct UnitColorInfo
        {
            public CharacterColor _colorType;
            public Sprite _colorSprite;
        }

        [Header("属性アイコン")]
        [SerializeField] UnitColorInfo[] _unitColorInfos;

        [Header("UI")]
        [SerializeField] CharaRoleIcon _roleIcon;
        [SerializeField] UnitRarityIcon _rarity;
        [SerializeField] UIText _unitName;
        [SerializeField] UIText _unitLevel;
        [SerializeField] UIText _unitLevelLimit;
        [SerializeField] UIText _summonCoolTime;
        [SerializeField] UIObject _summonCoolTimeArea;
        [SerializeField] UIText _summonCost;
        [SerializeField] UIImage _unitColor;
        [SerializeField] SeriesLogoComponent _seriesLogo;
        [SerializeField] UIObject _normalCharaStatusBG;
        [SerializeField] UIObject _specialCharaStatusBG;

        public void Setup(UnitEnhanceUnitInfoViewModel viewModel)
        {
            _roleIcon.SetupCharaRoleIcon(viewModel.RoleType);
            _rarity.Setup(viewModel.Rarity);
            _unitName.SetText(viewModel.Name.ToString());
            _unitLevel.SetText(viewModel.UnitLevel.ToString());
            _unitLevelLimit.SetText("/{0}", viewModel.UnitLevelLimit);
            _seriesLogo.Setup(viewModel.SeriesLogoImagePath);
            _summonCoolTime.SetText("{0}秒", viewModel.SummonCoolTime.ToSeconds());
            _summonCost.SetText(viewModel.SummonCost.ToString());
            SetUnitColor(viewModel.Color);

            // specialの場合、召喚CT非表示＆背景変更
            if (viewModel.RoleType == CharacterUnitRoleType.Special)
            {
                _summonCoolTimeArea.IsVisible = false;
                _normalCharaStatusBG.IsVisible = false;
                _specialCharaStatusBG.IsVisible = true;
            }
            else
            {
                _summonCoolTimeArea.IsVisible = true;
                _normalCharaStatusBG.IsVisible = true;
                _specialCharaStatusBG.IsVisible = false;
            }
        }

        void SetUnitColor(CharacterColor colorType)
        {
            foreach (var unitColorInfo in _unitColorInfos)
            {
                if (unitColorInfo._colorType == colorType)
                {
                    _unitColor.Sprite = unitColorInfo._colorSprite;
                    return;
                }
            }
        }
    }
}
