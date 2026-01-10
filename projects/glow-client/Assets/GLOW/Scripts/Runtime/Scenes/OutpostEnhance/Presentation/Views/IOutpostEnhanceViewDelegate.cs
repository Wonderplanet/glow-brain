using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.OutpostEnhance.Presentation.ViewModels;

namespace GLOW.Scenes.OutpostEnhance.Presentation.Views
{
    public interface IOutpostEnhanceViewDelegate
    {
        void OnViewWillAppear();
        void OnViewWillDisappear();
        void OnGateTypeButtonSelected(OutpostEnhanceTypeButtonViewModel buttonViewModel);
        void OnEnhanceButtonSelected(OutpostEnhanceTypeButtonViewModel buttonViewModel);
        void ChangeArtworkSelection(MasterDataId mstArtworkId);
        void ShowArtworkDetail(MasterDataId mstArtworkId, IReadOnlyList<MasterDataId> mstArtworkIds);
        void ShowArtworkList();
        void ShowEnhanceList();
    }
}
