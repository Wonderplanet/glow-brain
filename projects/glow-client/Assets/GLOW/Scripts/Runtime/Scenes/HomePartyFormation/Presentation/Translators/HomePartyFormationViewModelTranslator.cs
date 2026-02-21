using System.Linq;
using GLOW.Scenes.HomePartyFormation.Domain.Models;
using GLOW.Scenes.HomePartyFormation.Presentation.Presenters;
namespace GLOW.Scenes.HomePartyFormation.Presentation.Translators
{
    public class HomePartyFormationViewModelTranslator
    {
        public static HomePartyFormationViewModel TranslateHomePartyFormationViewModel(HomePartyFormationUseCaseModel homePartyFormationUseCaseModel)
        {
            var itemList = homePartyFormationUseCaseModel.UnitItems
                .ToDictionary(item => item.UserUnitId, item => item.IsAchievedSpecialRule);
            return new HomePartyFormationViewModel(itemList);
        }
    }
}

