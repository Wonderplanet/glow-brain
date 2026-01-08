using System;
using GLOW.Scenes.UnitList.Domain.Constants;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class CharacterStatusIconComponent : UIObject
    {
        [Serializable]
        struct StatusIconInfo
        {
            public UnitListSortType _sortType;
            public Sprite _icon;
        }

        [SerializeField] StatusIconInfo[] _statusIconInfos;
        [SerializeField] UIImage _statusIcon;

        public void Setup(UnitListSortType sortType)
        {
            foreach (var info in _statusIconInfos)
            {
                if (info._sortType != sortType) continue;

                this.Hidden = false;
                _statusIcon.Image.sprite = info._icon;
                return;
            }

            this.Hidden = true;
        }
    }
}
