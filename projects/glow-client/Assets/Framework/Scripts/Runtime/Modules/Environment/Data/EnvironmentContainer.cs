using System;

namespace WPFramework.Modules.Environment
{
    public class EnvironmentContainer : IEnvironmentContainer
    {
        EnvironmentListData _environmentListData = new EnvironmentListData(Array.Empty<EnvironmentData>());

        public void Save(EnvironmentListData environmentListData)
        {
            _environmentListData = environmentListData;
        }

        public EnvironmentListData Get()
        {
            return _environmentListData;
        }
    }
}
