using System;

namespace GLOW.Core.Presentation.Modules.Systems
{
    public class ContentMaintenanceCoordinator : IContentMaintenanceCoordinator
    {
        // シーンごとに外部からZenject経由で生成して設定する
        IContentMaintenanceHandler _contentMaintenanceHandler;
        void IContentMaintenanceCoordinator.SetUp(IContentMaintenanceHandler contentMaintenanceHandler)
        {
            _contentMaintenanceHandler = contentMaintenanceHandler;
        }

        bool IContentMaintenanceCoordinator.TryHandle(bool needsCleanup, Action completion)
        {
            if (_contentMaintenanceHandler == null)
            {
                return false;
            }

            return _contentMaintenanceHandler.TryHandle(needsCleanup, completion);
        }
    }
}
