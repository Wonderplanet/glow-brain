using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaTop.Presentation.Views
{
    public interface IEncyclopediaTopViewDelegate
    {
        void OnViewWillAppear();
        void OnSelectSeries(MasterDataId mstSeriesId);
        void OnSelectEncyclopediaBonusButton();
        void OnSelectSortButton();
        void OnClose();
    }
}
