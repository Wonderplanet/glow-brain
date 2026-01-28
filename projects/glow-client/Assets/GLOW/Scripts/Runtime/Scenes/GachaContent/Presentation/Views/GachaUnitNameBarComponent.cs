using System;
using System.Collections.Generic;
using System.Diagnostics.CodeAnalysis;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.GachaContent.Presentation.Views
{
    public class GachaUnitNameBarComponent : UIObject
    {
        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public class UnitNameRarityColor
        {
            public Rarity Rarity;
            public UIImage BaseColorBannerImage;
        }

        [SerializeField] UIText _unitName;
        [SerializeField] List<UnitNameRarityColor> _rarityColorList;
        [SerializeField] CharaRoleIcon _roleIcon;
        [SerializeField] CharacterColorIcon _colorIcon;
        [SerializeField] IconRarityImage _rarityIcon;

        public void Setup(Rarity rarity, CharacterName charaName, CharacterUnitRoleType roleType, CharacterColor characterColor)
        {
            _unitName.SetText(charaName.Value);
            _roleIcon.SetupCharaRoleIcon(roleType);
            _colorIcon.SetupCharaColorIcon(characterColor);
            _rarityIcon.Setup(rarity);

            foreach (var rarityColor in _rarityColorList)
            {
                rarityColor.BaseColorBannerImage.Hidden = (rarityColor.Rarity != rarity);
            }
        }
    }
}
