using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaUnitDetail.Presentation.Views
{
    public interface IEncyclopediaUnitDetailViewDelegate
    {
        void OnViewDidLoad();
        void OnSwitchUnit(MasterDataId mstUnitId);
        void OnBackButtonTapped();
        void OnPlaySpecialAttackButtonTapped();
    }
}
