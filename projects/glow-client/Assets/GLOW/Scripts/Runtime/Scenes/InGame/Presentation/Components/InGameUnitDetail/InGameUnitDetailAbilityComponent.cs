using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using GLOW.Scenes.UnitEnhance.Presentation.Views.Components;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components.InGameUnitDetail
{
    public class InGameUnitDetailAbilityComponent : UIObject
    {
        [SerializeField] List<UnitEnhanceAbilityCellComponent> _abilityList;
        [SerializeField] UnitEnhanceAbilityCellComponent _instantiateAbilityCellPrefab;
        [SerializeField] Transform _abilityCellParent;

        public void Setup(IReadOnlyList<UnitEnhanceAbilityViewModel> viewModelList)
        {
            foreach (var cell in _abilityList)
            {
                cell.gameObject.SetActive(false);
            }
            
            // 足りない分を生成
            DuplicateShortageAbilityCell(viewModelList);

            // 特性なしの場合
            if (viewModelList.Count == 0)
            {
                if (_abilityList.Count == 0) return;

                _abilityList[0].gameObject.SetActive(true);
                _abilityList[0].Setup(UnitEnhanceAbilityViewModel.Empty);
                return;
            }

            for (int i = 0; i < viewModelList.Count; i++)
            {
                if (i >= _abilityList.Count) break;

                _abilityList[i].gameObject.SetActive(true);
                _abilityList[i].Setup(viewModelList[i]);
            }
        }
        
        void DuplicateShortageAbilityCell(IReadOnlyList<UnitEnhanceAbilityViewModel> viewModelList)
        {
            int shortageCount = viewModelList.Count - _abilityList.Count;
            if(shortageCount <= 0) return;

            for (int i = 0; i < shortageCount; i++)
            {
                var cell = Instantiate(_instantiateAbilityCellPrefab, _abilityCellParent);
                cell.gameObject.SetActive(false);
                _abilityList.Add(cell);
            }
        }
    }
}
