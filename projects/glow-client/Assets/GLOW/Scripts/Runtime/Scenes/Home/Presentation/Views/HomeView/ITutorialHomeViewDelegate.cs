namespace GLOW.Scenes.Home.Presentation.Views
{
    public interface ITutorialHomeViewDelegate
    {
        bool IsPresented<T>();
        void EnableHomeHeaderTap();
        void DisableHomeHeaderTap();
    }
}