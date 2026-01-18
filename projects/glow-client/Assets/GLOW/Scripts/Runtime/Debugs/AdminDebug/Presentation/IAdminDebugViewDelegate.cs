using GLOW.Debugs.AdminDebug.Domain.Models;

namespace GLOW.Debugs.AdminDebug.Presentation
{
    public interface IAdminDebugViewDelegate
    {
        void OnViewDidLoad(AdminDebugViewController viewController);
        void OnViewWillAppear();
        void OnViewDidUnload();
        void OnSelectCommand(AdminDebugMenuCommandModel command);
    }
}
