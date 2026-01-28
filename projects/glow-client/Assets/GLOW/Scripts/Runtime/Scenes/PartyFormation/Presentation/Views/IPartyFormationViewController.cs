using System.Threading;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PartyFormation.Presentation.ViewModels;
using UIKit;

namespace GLOW.Scenes.PartyFormation.Presentation.Views
{
    public interface IPartyFormationViewController
    {
        void InitializeView(PartyFormationInitializeViewModel viewModel);
        CancellationToken GetCancellationTokenOnDestroy();
        void PresentModally(UIViewController controller);
        void UpdatePartyView(PartyNo partyNo);
        void SetPartyNo(PartyNo partyNo);
        void UpdateUnitList(PartyFormationUnitListViewModel viewModel);
        void UpdateSortAndFilterButton(bool isAnyFilter);
        void PlayUnitListCellAppearanceAnimation();
    }
}
