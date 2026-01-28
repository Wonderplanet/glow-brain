using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components.InGameUnitDetail
{
    public class InGameUnitNameComponent : UIObject
    {
        [SerializeField] UIText _unitNameText;
        [SerializeField] UnitRarityIcon _rarityIcon;
        
        public void Setup(CharacterName unitName, Rarity rarity)
        {
            _unitNameText.SetText(unitName.ToString());
            _rarityIcon.Setup(rarity);
        }
    }
}