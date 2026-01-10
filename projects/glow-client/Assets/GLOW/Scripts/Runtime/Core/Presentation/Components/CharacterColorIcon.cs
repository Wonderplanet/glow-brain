using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Extensions;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class CharacterColorIcon : UIImage
    {
        [Serializable]
        class CharacterColorSerialiable
        {
            [SerializeField] CharacterColor _type;
            [SerializeField] Sprite _icon;
            public CharacterColor Type => _type;
            public Sprite Icon => _icon;
        }

        [SerializeField] CharacterColorSerialiable[] _charaColors;

        public void SetupCharaColorIcon(CharacterColor characterColor)
        {
            if (characterColor == CharacterColor.None) Hidden = true;
            else
            {
                Sprite = GetIcon(characterColor);
                Hidden = false;
            }
        }

        Sprite GetIcon(CharacterColor characterColor)
        {
            return _charaColors.Find(icon => icon.Type == characterColor)?.Icon;
        }
    }
}
