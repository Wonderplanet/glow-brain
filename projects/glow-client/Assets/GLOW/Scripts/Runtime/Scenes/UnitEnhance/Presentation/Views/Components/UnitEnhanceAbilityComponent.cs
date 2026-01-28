using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.UnitEnhance.Presentation.Views.Components
{
    public class UnitEnhanceAbilityComponent : UIObject
    {
        [SerializeField] UnitEnhanceAbilityCellComponent[] _abilityCells;

        public void Setup(IReadOnlyList<UnitEnhanceAbilityViewModel> viewModelList)
        {
            foreach (var cell in _abilityCells)
            {
                cell.gameObject.SetActive(false);
            }

            // 特性なしの場合
            if (viewModelList.Count == 0)
            {
                return;
            }

            for (int i = 0; i < viewModelList.Count; i++)
            {
                if (i >= _abilityCells.Length) break;

                _abilityCells[i].gameObject.SetActive(true);
                _abilityCells[i].Setup(viewModelList[i]);
            }
        }
    }
}
