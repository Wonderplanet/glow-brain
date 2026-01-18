using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UserEmblem.Presentation.Views
{
    public interface IUserEmblemViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidAppear();
        void OnViewDidUnload();
        void OnCloseSelected();
        void OnSeriesTabSelected();
        void OnEventTabSelected();
        void OnIconTapped(MasterDataId avatarId);
    }
}
