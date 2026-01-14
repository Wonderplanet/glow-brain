namespace GLOW.Core.Presentation.PageContent
{
    public interface IPageContentViewControllerPageControlDataSource
    {
        int PresentationCount(PageContentViewController pageViewController);
        int PresentationIndex(PageContentViewController pageViewController);
    }
}
