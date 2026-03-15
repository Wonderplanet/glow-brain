using System;

namespace GLOW.Core.Presentation.Modules.Systems
{
    public interface IContentMaintenanceHandler
    {
        bool TryHandle(bool needsCleanup, Action completion);
    }
}
