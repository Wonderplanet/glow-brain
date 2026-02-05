using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.UnitEnhance.Presentation.Views.Components
{
    public class UnitEnhanceUnitDetailComponent : UIObject
    {
        [SerializeField] UIText _description;

        public void Setup(UnitEnhanceUnitDetailViewModel viewModel)
        {
            _description.SetText(viewModel.Detail.Value);
        }
    }
}
