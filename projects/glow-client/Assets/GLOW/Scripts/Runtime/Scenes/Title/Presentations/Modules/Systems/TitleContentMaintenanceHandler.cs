using System;
using GLOW.Core.Presentation.Modules.Systems;

namespace GLOW.Scenes.Title.Presentations.Modules.Systems
{
    public class TitleContentMaintenanceHandler : IContentMaintenanceHandler
    {
        bool IContentMaintenanceHandler.TryHandle(bool needsCleanup, Action completion)
        {
            // タイトルからのホーム遷移は限られた状況にしたいので、
            // タイトルはSessionResumePresenterでハンドリングしている
            return false;
        }
    }
}
