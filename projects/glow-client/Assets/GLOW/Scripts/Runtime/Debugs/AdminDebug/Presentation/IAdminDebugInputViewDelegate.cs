using System.Collections.Generic;

namespace GLOW.Debugs.AdminDebug.Presentation
{
    public interface IAdminDebugInputViewDelegate
    {
        void OnViewDidLoad(AdminDebugInputViewController viewController);
        void OnSubmit(string command, Dictionary<string, object> parameters);
    }
}
