namespace GLOW.Scenes.ItemBox.Presentation.ValueObjects
{
    public record FragmentLineupListDataSourceIndex(int Value)
    {
        public static FragmentLineupListDataSourceIndex Empty { get; } = new (0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
