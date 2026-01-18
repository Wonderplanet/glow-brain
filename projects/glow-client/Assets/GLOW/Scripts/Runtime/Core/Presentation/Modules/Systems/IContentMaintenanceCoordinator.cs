using System;
using WPFramework.Presentation.Modules;

namespace GLOW.Core.Presentation.Modules.Systems
{
    public interface IContentMaintenanceCoordinator
    {
        void SetUp(IContentMaintenanceHandler contentMaintenanceHandler);
        bool TryHandle(bool needsCleanup, Action completion);
    }
}
