namespace GLOW.Debugs.AdminDebug.Presentation
{
    public record AdminDebugInputViewModel(
        string Command,
        string Name,
        string Description,
        AdminDebugInputCellViewModel[] CellViewModels)
    {
        public string Command { get; } = Command;
        public string Name { get; } = Name;
        public string Description { get; } = Description;
        public AdminDebugInputCellViewModel[] CellViewModels { get; } = CellViewModels;
    }
}
