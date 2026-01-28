using System.Threading;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.HomePartyFormation.Presentation.Presenters;
using GLOW.Scenes.PartyFormation.Presentation.ViewModels;
using UIKit;

namespace GLOW.Scenes.HomePartyFormation.Presentation.Views
{
    public interface IHomePartyFormationViewController
    {
        void InitializeView(PartyFormationInitializeViewModel viewModel);
        CancellationToken GetCancellationTokenOnDestroy();
        void PresentModally(UIViewController controller);
        void UpdatePartyView(PartyNo partyNo);
        void UpdateUnitList(PartyFormationUnitListViewModel viewModel, HomePartyFormationViewModel homePartyFormationViewModel);
        void UpdateSortAndFilterButton(bool isAnyFilter);
        void PlayUnitListCellAppearanceAnimation();
    }
}
