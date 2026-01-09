using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitList.Presentation.Views
{
    public interface IUnitListViewDelegate
    {
        void ViewWillAppear();
        void OnSelectUnit(UserDataId userUnitId);
        void OnSortAndFilter();
        void OnSortAscending();
        void OnSortDescending();
    }
}
