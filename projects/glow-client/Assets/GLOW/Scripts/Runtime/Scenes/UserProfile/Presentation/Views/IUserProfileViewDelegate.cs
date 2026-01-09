using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UserProfile.Presentation.Views
{
    public interface IUserProfileViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidAppear();
        void OnViewDidUnload();
        void OnChangeNameSelected();
        void OnCloseSelected();
        void OnIconTapped(MasterDataId avatarId);
    }
}
