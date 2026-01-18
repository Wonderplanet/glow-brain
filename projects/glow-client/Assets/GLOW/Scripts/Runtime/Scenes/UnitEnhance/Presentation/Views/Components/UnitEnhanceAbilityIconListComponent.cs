using System.Collections.Generic;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.UnitEnhance.Presentation.Views.Components
{
    public class UnitEnhanceAbilityIconListComponent : MonoBehaviour
    {
        [SerializeField] List<UnitEnhanceAbilityIconComponent> _abilityIcons;

        public void SetupAbilityIcons(IReadOnlyList<UnitEnhanceAbilityViewModel> abilities)
        {
            for (var i = 0; i < _abilityIcons.Count; i++)
            {
                var icon = _abilityIcons[i];
                if (i < abilities.Count)
                {
                    var viewModel = abilities[i];
                    icon.Setup(viewModel.Ability, viewModel.IsLock);
                    icon.OnClick.RemoveAllListeners();
                    icon.OnClick.AddListener(() =>
                    {
                        var hidden = icon.HiddenDescription;
                        HiddenAllDescriptions();
                        icon.HiddenDescription = !hidden;
                    });
                }
                else
                {
                    icon.gameObject.SetActive(false);
                }
            }

            HiddenAllDescriptions();
        }

        public void HiddenAllDescriptions()
        {
            foreach (var icon in _abilityIcons)
            {
                icon.HiddenDescription = true;
            }

        }
    }
}
