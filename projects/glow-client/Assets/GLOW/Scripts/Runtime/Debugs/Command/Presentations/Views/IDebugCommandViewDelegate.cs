namespace GLOW.Debugs.Command.Presentations.Views
{
    public interface IDebugCommandViewDelegate
    {
        void OnViewDidLoad(DebugCommandViewController viewController);
        void ViewDidUnload();
    }
}
