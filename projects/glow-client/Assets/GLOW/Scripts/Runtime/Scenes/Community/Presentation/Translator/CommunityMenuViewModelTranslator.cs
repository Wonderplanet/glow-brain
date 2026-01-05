using GLOW.Scenes.Community.Domain.Model;
using GLOW.Scenes.Community.Presentation.ViewModel;

namespace GLOW.Scenes.Community.Presentation.Translator
{
    public class CommunityMenuViewModelTranslator
    {
        public static CommunityMenuViewModel ToCommunityMenuViewModel(CommunityListUseCaseModel model)
        {
            
            return new CommunityMenuViewModel(
                JumbleRushOfficialSiteCellViewModel: ToCommunityMenuCellViewModel(model.JumbleRushOfficialSiteCommunityListModel),
                JumbleRushOfficialXCellViewModel: ToCommunityMenuCellViewModel(model.JumbleRushOfficialXCommunityListModel),
                JumpPlusOfficialSiteCellViewModel: ToCommunityMenuCellViewModel(model.JumpPlusOfficialSiteCommunityListModel),
                JumpPlusLinkCellViewModel: ToCommunityMenuCellViewModel(model.JumpPlusLinkCommunityListModel),
                JumpPlusOfficialXCellViewModel: ToCommunityMenuCellViewModel(model.JumpPlusOfficialXCommunityListModel));
        }
        
        static CommunityMenuCellViewModel ToCommunityMenuCellViewModel(CommunityListModel model)
        {
            return new CommunityMenuCellViewModel(
                model.CommunityUrl,
                model.CommunityUrlSchema);
        }
    }
}