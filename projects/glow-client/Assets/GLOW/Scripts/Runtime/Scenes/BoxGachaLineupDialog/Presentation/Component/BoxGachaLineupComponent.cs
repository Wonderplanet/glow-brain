using System.Collections.Generic;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.BoxGachaLineupDialog.Presentation.Component
{
    public class BoxGachaLineupComponent : UIObject
    {
        [SerializeField] UIText _rarityAndNumber;
        [SerializeField] BoxGachaLineupCellComponent _cellPrefab;
        [SerializeField] Transform _contentRoot;
        
        public void SetupHeaderText(Rarity rarity, int count)
        {
            _rarityAndNumber.SetText(ZString.Format("{0}(全{1}種)", rarity.ToString(), count));
        }

        public BoxGachaLineupCellComponent InstantiateCell()
        {
            return Instantiate(_cellPrefab, _contentRoot);
        }
    }
}