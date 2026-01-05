using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaEnemyDetail.Presentation.Views
{
    public interface IEncyclopediaEnemyDetailViewDelegate
    {
        void OnViewDidLoad();
        void OnSwitchUnit(MasterDataId mstUnitId);
        void OnBackButtonTapped();
    }
}
