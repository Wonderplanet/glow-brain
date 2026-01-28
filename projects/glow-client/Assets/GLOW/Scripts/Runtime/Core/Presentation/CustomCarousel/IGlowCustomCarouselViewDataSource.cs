namespace GLOW.Core.Presentation.CustomCarousel
{
    public interface IGlowCustomCarouselViewDataSource
    {
        int NumberOfItems();
        int SelectedIndex();
        GlowCustomInfiniteCarouselCell CellForItemAtIndex(int index);
    }
}
