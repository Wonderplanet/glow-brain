using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Extensions;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class CharaRoleIcon : UIImage
    {
        [Serializable]
        class RoleIcon
        {
            [SerializeField] CharacterUnitRoleType _roleType;
            [SerializeField] Sprite _icon;
            public CharacterUnitRoleType RoleType => _roleType;
            public Sprite Icon => _icon;
        }

        [SerializeField] RoleIcon[] _charaRoleIcons;

        public void SetupCharaRoleIcon(CharacterUnitRoleType characterUnitRoleType)
        {
            Sprite = GetRoleIcon(characterUnitRoleType);
            Hidden = false;
        }

        Sprite GetRoleIcon(CharacterUnitRoleType characterUnitRoleType)
        {
            return _charaRoleIcons.Find(icon => icon.RoleType == characterUnitRoleType)?.Icon;
        }
    }
}
