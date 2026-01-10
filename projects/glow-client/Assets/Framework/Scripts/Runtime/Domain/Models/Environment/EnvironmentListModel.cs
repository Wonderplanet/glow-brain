namespace WPFramework.Domain.Models
{
    public partial record EnvironmentListModel(EnvironmentModel[] Environments)
    {
        public EnvironmentModel[] Environments { get; } = Environments;

        public void Swap(int to, int from)
        {
            (Environments[to], Environments[from]) = (Environments[from], Environments[to]);
        }
    }
}
