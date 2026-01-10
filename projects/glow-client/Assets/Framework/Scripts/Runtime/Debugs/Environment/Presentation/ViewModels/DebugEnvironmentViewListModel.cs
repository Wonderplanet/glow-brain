namespace WPFramework.Debugs.Environment.Presentation.ViewModels
{
    public partial record DebugEnvironmentViewListModel(DebugEnvironmentViewModel[] Environments)
    {
        public DebugEnvironmentViewModel[] Environments { get; } = Environments;
    }
}
