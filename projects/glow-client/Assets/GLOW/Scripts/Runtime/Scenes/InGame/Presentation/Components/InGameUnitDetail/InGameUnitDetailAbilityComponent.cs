using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.InGame.Presentation.ViewModels.InGameUnitDetail;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using GLOW.Scenes.UnitEnhance.Presentation.Views.Components;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components.InGameUnitDetail
{
    public class InGameUnitDetailAbilityComponent : UIObject
    {
        [SerializeField] UnitEnhanceAbilityCellComponent[] _abilityList;

        public void Setup(IReadOnlyList<UnitEnhanceAbilityViewModel> viewModelList)
        {
            foreach (var cell in _abilityList)
            {
                cell.gameObject.SetActive(false);
            }

            // 特性なしの場合
            if (viewModelList.Count == 0)
            {
                if (_abilityList.Length == 0) return;

                _abilityList[0].gameObject.SetActive(true);
                _abilityList[0].Setup(UnitEnhanceAbilityViewModel.Empty);
                return;
            }

            for (int i = 0; i < viewModelList.Count; i++)
            {
                if (i >= _abilityList.Length) break;

                _abilityList[i].gameObject.SetActive(true);
                _abilityList[i].Setup(viewModelList[i]);
            }
        }
    }
}
