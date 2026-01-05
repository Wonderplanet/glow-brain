using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class UnitRarityIcon : MonoBehaviour
    {
        [Serializable]
        public class RarityIcon
        {
            [SerializeField] Rarity _rarity;
            [SerializeField] GameObject _iconObj;

            public Rarity Rarity => _rarity;
            public GameObject Obj => _iconObj;
        }

        [SerializeField] List<RarityIcon> _rarityIcons;

        public void Setup(Rarity rarity)
        {
            foreach (var icon in _rarityIcons)
            {
                icon.Obj.SetActive(icon.Rarity == rarity);
            }
        }
    }
}
