namespace WPFramework.Presentation.Views
{
    public interface INavigationViewContext
    {
        NavigationBackgroundItem NavigationBackgroundItem { get; }
        string ContextTitle { get; }
    }
}
