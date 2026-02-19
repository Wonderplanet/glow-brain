namespace WPFramework.Modules.Environment
{
    public interface IEnvironmentContainer
    {
        void Save(EnvironmentListData environmentListData);
        EnvironmentListData Get();
    }
}
